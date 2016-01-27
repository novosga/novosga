<?php

namespace Novosga\MonitorBundle\Controller;

use Exception;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use Novosga\Entity\Unidade;
use Novosga\Service\AtendimentoService;
use Novosga\Service\FilaService;
use Novosga\Service\ServicoService;
use Novosga\Util\Arrays;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * DefaultController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends Controller
{
    
    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/", name="novosga_monitor_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $unidade = $request->getSession()->get('unidade');
        $servicos = $this->servicos($unidade, ' e.status = 1 ');
        
        // lista de prioridades para ser utilizada ao redirecionar senha
        $prioridades = $em
                    ->getRepository(\Novosga\Entity\Prioridade::class)
                    ->findBy([
                        'status' => 1
                    ], [
                        'peso' => 'ASC',
                        'nome' => 'ASC'
                    ]);
        
        return $this->render('NovosgaMonitorBundle:Default:index.html.twig', [
            'unidade' => $unidade,
            'servicos' => $servicos,
            'prioridades' => $prioridades,
            'milis' => time() * 1000
        ]);
    }

    private function servicos(Unidade $unidade, $where = '')
    {
        $em = $this->getDoctrine()->getManager();
        $service = new ServicoService($em);

        return $service->servicosUnidade($unidade, $where);
    }

    /**
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * 
     * @Route("/ajax_update", name="novosga_monitor_ajaxupdate")
     */
    public function ajaxUpdateAction(Request $request)
    {
        $response = new JsonResponse();
        $unidade = $context->getUnidade();
        $filaService = new FilaService($this->em());
        if ($unidade) {
            $ids = $context->request()->get('ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (count($ids)) {
                $response->data['total'] = 0;
                $servicos = $this->servicos($unidade, ' e.servico IN ('.implode(',', $ids).') ');
                $em = $context->database()->createEntityManager();
                if ($servicos) {
                    foreach ($servicos as $su) {
                        $rs = $filaService->filaServico($unidade, $su->getServico());
                        $total = count($rs);
                        // prevent overhead
                        if ($total) {
                            $fila = [];
                            foreach ($rs as $atendimento) {
                                $arr = $atendimento->jsonSerialize(true);
                                $fila[] = $arr;
                            }
                            $response->data['servicos'][$su->getServico()->getId()] = $fila;
                            ++$response->data['total'];
                        }
                    }
                }
                $response->success = true;
            }
        }

        return $response;
    }

    public function info_senha(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $id = (int) $context->request()->get('id');
            $service = new AtendimentoService($this->em());
            $atendimento = $service->buscaAtendimento($unidade, $id);
            if ($atendimento) {
                $response->data = $atendimento->jsonSerialize();
                $response->success = true;
            } else {
                $response->message = _('Atendimento inválido');
            }
        }

        return $response;
    }

    /**
     * Busca os atendimentos a partir do número da senha.
     *
     * @param Novosga\Context $context
     */
    public function buscar(Context $context)
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

    /**
     * Transfere o atendimento para outro serviço e prioridade.
     *
     * @param Novosga\Context $context
     */
    public function transferir(Context $context)
    {
        $response = new JsonResponse();
        try {
            $unidade = $context->getUser()->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
            $id = (int) $context->request()->post('id');
            $atendimento = $this->getAtendimento($unidade, $id);
            /*
             * TODO: verificar se o servico informado esta disponivel para a unidade.
             */
            $servico = (int) $context->request()->post('servico');
            $prioridade = (int) $context->request()->post('prioridade');

            $service = new AtendimentoService($this->em());
            $response->success = $service->transferir($atendimento, $unidade, $servico, $prioridade);
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * Reativa o atendimento para o mesmo serviço e mesma prioridade.
     * Só pode reativar atendimentos que foram: Cancelados ou Não Compareceu.
     *
     * @param Novosga\Context $context
     */
    public function reativar(Context $context)
    {
        $response = new JsonResponse();
        try {
            $unidade = $context->getUser()->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
            $id = (int) $context->request()->post('id');
            $conn = $this->em()->getConnection();
            $status = implode(',', [AtendimentoService::SENHA_CANCELADA, AtendimentoService::NAO_COMPARECEU]);
            // reativa apenas se estiver finalizada (data fim diferente de nulo)
            $stmt = $conn->prepare("
                UPDATE
                    atendimentos
                SET
                    status = :status,
                    dt_fim = NULL
                WHERE
                    id = :id AND
                    unidade_id = :unidade AND
                    status IN ({$status})
            ");
            $stmt->bindValue('id', $id);
            $stmt->bindValue('status', AtendimentoService::SENHA_EMITIDA);
            $stmt->bindValue('unidade', $unidade->getId());
            $response->success = $stmt->execute() > 0;
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    /**
     * Atualiza o status da senha para cancelado.
     *
     * @param Novosga\Context $context
     */
    public function cancelar(Context $context)
    {
        $response = new JsonResponse();
        try {
            $unidade = $context->getUser()->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
            $id = (int) $context->request()->post('id');
            $atendimento = $this->getAtendimento($unidade, $id);
            $service = new AtendimentoService($this->em());
            $response->success = $service->cancelar($atendimento, $unidade);
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }

        return $response;
    }

    private function getAtendimento(Unidade $unidade, $id)
    {
        $atendimento = $this->em()->find('Novosga\Entity\Atendimento', $id);
        if (!$atendimento || $atendimento->getServicoUnidade()->getUnidade()->getId() != $unidade->getId()) {
            throw new Exception(_('Atendimento inválido'));
        }
        if (!$atendimento) {
            throw new Exception(_('Atendimento inválido'));
        }

        return $atendimento;
    }
}
