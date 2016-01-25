<?php

namespace Novosga\TriagemBundle\Controller;

use Exception;
use Novosga\Context;
use Novosga\Http\JsonResponse;
use AppBundle\Entity\Unidade;
use Novosga\Service\AtendimentoService;
use Novosga\Service\ServicoService;
use Novosga\Util\Arrays;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DefaultController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DefaultController extends Controller
{
    
    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/", name="novosga_triagem_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $unidade = $request->getSession()->get('unidade');
        
        $prioridades = $em->getRepository(\AppBundle\Entity\Prioridade::class)->findAtivas();
        $servicos = $this->getServicoService()->servicosUnidade($unidade, 'e.status = 1');
        
        return $this->render('NovosgaTriagemBundle:Default:index.html.twig', [
            'unidade' => $unidade,
            'servicos' => $servicos,
            'prioridades' => $prioridades,
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/imprimir", name="novosga_triagem_print")
     */
    public function imprimirAction(Request $request)
    {
        $id = (int) $context->request()->get('id');
        $ctrl = new \Novosga\Controller\TicketController($this->app());

        return $ctrl->printTicket($ctrl->getAtendimento($id));
    }

    /**
     * @param Request $request
     * @return Response
     * 
     * @Route("/ajax_update", name="novosga_triagem_ajax_update")
     */
    public function ajaxUpdateAction(Request $request)
    {
        $response = new JsonResponse();
        $unidade = $request->getSession()->get('unidade');
        
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
        $unidade = $request->getSession()->get('unidade');
        $usuario = $this->getUser();
        
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

    /**
     * @return ServicoService
     */
    private function getServicoService()
    {
        $service = new ServicoService($this->getDoctrine()->getManager());

        return $service;
    }
}
