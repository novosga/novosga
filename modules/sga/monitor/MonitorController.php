<?php

namespace modules\sga\monitor;

use Exception;
use Novosga\Context;
use Novosga\Util\Arrays;
use Novosga\Model\Unidade;
use Novosga\Http\JsonResponse;
use Novosga\Controller\ModuleController;
use Novosga\Service\AtendimentoService;
use Novosga\Service\FilaService;
use Novosga\Service\ServicoService;

/**
 * MonitorController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MonitorController extends ModuleController
{
    public function index(Context $context)
    {
        $unidade = $context->getUser()->getUnidade();
        $this->app()->view()->set('unidade', $unidade);
        if ($unidade) {
            // servicos
            $this->app()->view()->set('servicos', $this->servicos($unidade, ' e.status = 1 '));
        }
        // lista de prioridades para ser utilizada ao redirecionar senha
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Prioridade e WHERE e.status = 1 ORDER BY e.peso, e.nome");
        $this->app()->view()->set('prioridades', $query->getResult());
        $this->app()->view()->set('milis', time() * 1000);
    }

    private function servicos(Unidade $unidade, $where = '')
    {
        $service = new ServicoService($this->em());

        return $service->servicosUnidade($unidade, $where);
    }

    public function ajax_update(Context $context)
    {
        $response = new JsonResponse();
        $unidade = $context->getUnidade();
        $filaService = new FilaService($this->em());
        if ($unidade) {
            $ids = $context->request()->get('ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (sizeof($ids)) {
                $response->data['total'] = 0;
                $servicos = $this->servicos($unidade, ' e.servico IN ('.implode(',', $ids).') ');
                $em = $context->database()->createEntityManager();
                if ($servicos) {
                    foreach ($servicos as $su) {
                        $rs = $filaService->filaServico($unidade, $su->getServico());
                        $total = count($rs);
                        // prevent overhead
                        if ($total) {
                            $fila = array();
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
            $response->data['total'] = sizeof($atendimentos);
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
            $status = implode(',', array(AtendimentoService::SENHA_CANCELADA, AtendimentoService::NAO_COMPARECEU));
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
        $atendimento = $this->em()->find('Novosga\Model\Atendimento', $id);
        if (!$atendimento || $atendimento->getServicoUnidade()->getUnidade()->getId() != $unidade->getId()) {
            throw new Exception(_('Atendimento inválido'));
        }
        if (!$atendimento) {
            throw new Exception(_('Atendimento inválido'));
        }

        return $atendimento;
    }
}
