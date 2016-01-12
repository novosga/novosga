<?php

namespace Novosga\TriagemBundle\Controller;

use Exception;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use AppBundle\Entity\Unidade;
use Novosga\Service\AtendimentoService;
use Novosga\Service\ServicoService;
use Novosga\Util\Arrays;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * TriagemController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class TriagemController extends Controller
{
    public function index(Context $context)
    {
        $unidade = $context->getUser()->getUnidade();
        $this->app()->view()->set('unidade', $unidade);
        if ($unidade) {
            $this->app()->view()->set('servicos', $this->servicos($unidade));
        }
        $query = $this->em()->createQuery("SELECT e FROM AppBundle\Entity\Prioridade e WHERE e.status = 1 AND e.peso > 0 ORDER BY e.nome");
        $this->app()->view()->set('prioridades', $query->getResult());
    }

    private function servicos(Unidade $unidade)
    {
        $service = new ServicoService($this->em());

        return $service->servicosUnidade($unidade, 'e.status = 1');
    }

    public function imprimir(Context $context)
    {
        $id = (int) $context->request()->get('id');
        $ctrl = new \Novosga\Controller\TicketController($this->app());

        return $ctrl->printTicket($ctrl->getAtendimento($id));
    }

    public function ajax_update(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            $ids = $context->request()->get('ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            $senhas = [];
            if (count($ids)) {
                $dql = "
                    SELECT
                        s.id, COUNT(e) as total
                    FROM
                        AppBundle\Entity\Atendimento e
                        JOIN e.servico s
                    WHERE
                        e.unidade = :unidade AND
                        e.servico IN (:servicos)
                ";
                // total senhas do servico (qualquer status)
                $rs = $this->em()
                        ->createQuery($dql.' GROUP BY s.id')
                        ->setParameter('unidade', $unidade)
                        ->setParameter('servicos', $ids)
                        ->getArrayResult();
                foreach ($rs as $r) {
                    $senhas[$r['id']] = ['total' => $r['total'], 'fila' => 0];
                }
                // total senhas esperando
                $rs = $this->em()
                        ->createQuery($dql.' AND e.status = :status GROUP BY s.id')
                        ->setParameter('unidade', $unidade)
                        ->setParameter('servicos', $ids)
                        ->setParameter('status', AtendimentoService::SENHA_EMITIDA)
                        ->getArrayResult();
                foreach ($rs as $r) {
                    $senhas[$r['id']]['fila'] = $r['total'];
                }

                $service = new AtendimentoService($this->em());

                $response->success = true;
                $response->data = [
                    'ultima'   => $service->ultimaSenhaUnidade($unidade),
                    'servicos' => $senhas,
                ];
            }
        }

        return $response;
    }

    public function servico_info(Context $context)
    {
        $response = new JsonResponse();
        $id = (int) $context->request()->get('id');
        try {
            $servico = $this->em()->find("AppBundle\Entity\Servico", $id);
            if (!$servico) {
                throw new Exception(_('Serviço inválido'));
            }
            $response->data['nome'] = $servico->getNome();
            $response->data['descricao'] = $servico->getDescricao();

            // ultima senha
            $service = new AtendimentoService($this->em());
            $atendimento = $service->ultimaSenhaServico($context->getUnidade(), $servico);
            if ($atendimento) {
                $response->data['senha'] = $atendimento->getSenha()->toString();
                $response->data['senhaId'] = $atendimento->getId();
            } else {
                $response->data['senha'] = '-';
                $response->data['senhaId'] = '';
            }
            // subservicos
            $response->data['subservicos'] = [];
            $query = $this->em()->createQuery("SELECT e FROM AppBundle\Entity\Servico e WHERE e.mestre = :mestre ORDER BY e.nome");
            $query->setParameter('mestre', $servico->getId());
            $subservicos = $query->getResult();
            foreach ($subservicos as $s) {
                $response->data['subservicos'][] = $s->getNome();
            }
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    public function distribui_senha(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUnidade();
        $usuario = $context->getUser();
        $servico = (int) $context->request()->post('servico');
        $prioridade = (int) $context->request()->post('prioridade');
        $nomeCliente = $context->request()->post('cli_nome', '');
        $documentoCliente = $context->request()->post('cli_doc', '');
        try {
            $service = new AtendimentoService($this->em());
            $response->data = $service->distribuiSenha($unidade, $usuario, $servico, $prioridade, $nomeCliente, $documentoCliente)->jsonSerialize();
            $response->success = true;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
            $response->success = false;
        }

        return $response;
    }

    /**
     * Busca os atendimentos a partir do número da senha.
     *
     * @param Context $context
     */
    public function consulta_senha(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = $context->request()->get('numero');
            $service = new AtendimentoService($this->em());
            $atendimentos = $service->buscaAtendimentos($unidade, $numero);
            $response->data['total'] = count($atendimentos);
            foreach ($atendimentos as $atendimento) {
                $response->data['atendimentos'][] = $atendimento->jsonSerialize();
            }
            $response->success = true;
        } else {
            $response->message = _('Nenhuma unidade selecionada');
        }

        return $response;
    }
}
