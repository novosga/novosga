<?php
namespace modules\sga\monitor;

use Exception;
use Novosga\Context;
use Novosga\Util\Arrays;
use Novosga\Util\DateUtil;
use Novosga\Model\Unidade;
use Novosga\Http\JsonResponse;
use Novosga\Controller\ModuleController;
use Novosga\Business\AtendimentoBusiness;
use Novosga\Business\FilaBusiness;

/**
 * MonitorController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MonitorController extends ModuleController {

    public function index(Context $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->app()->view()->set('unidade', $unidade);
        if ($unidade) {
            // servicos
            $this->app()->view()->set('servicos', $this->servicos($unidade));
        }
        // lista de prioridades para ser utilizada ao redirecionar senha
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Prioridade e WHERE e.status = 1 ORDER BY e.peso, e.nome");
        $this->app()->view()->set('prioridades', $query->getResult());
        $this->app()->view()->set('milis', time() * 1000);
    }
    
    private function servicos(Unidade $unidade, $where = "") {
        $dql = "SELECT e FROM Novosga\Model\ServicoUnidade e WHERE e.unidade = :unidade AND e.status = 1";
        if (!empty($where)) {
            $dql .= " AND $where";
        }
        $dql .= " ORDER BY e.nome";
        $query = $this->em()->createQuery($dql);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }
    
    public function ajax_update(Context $context) {
        $response = new JsonResponse();
        $unidade = $context->getUnidade();
        $filaBusiness = new FilaBusiness($this->em());
        if ($unidade) {
            $ids = $context->request()->get('ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (sizeof($ids)) {
                $response->data['total'] = 0;
                $servicos = $this->servicos($unidade, " e.servico IN (" . implode(',', $ids) . ") ");
                $em = $context->database()->createEntityManager();
                if ($servicos) {
                    foreach ($servicos as $su) {
                        $rs = $filaBusiness
                                    ->servico($unidade, $su->getServico())
                                    ->getQuery()
                                    ->getResult()
                        ;
                        $total = count($rs);
                        // prevent overhead
                        if ($total) {
                            $fila = array();
                            foreach ($rs as $atendimento) {
                                $arr = $atendimento->toArray(true);
                                $fila[] = $arr;
                            }
                            $response->data['servicos'][$su->getServico()->getId()] = $fila;
                            $response->data['total']++;
                        }
                    }
                }
                $response->success = true;
            }
        }
        return $response;
    }
    
    public function info_senha(Context $context) {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $id = (int) $context->request()->get('id');
            $ab = new AtendimentoBusiness($this->em());
            $atendimento = $ab->buscaAtendimento($unidade, $id);
            if ($atendimento) {
                $response->data = $atendimento->toArray();
                $response->success = true;
            } else {
                $response->message = _('Atendimento inválido');
            }
        }
        return $response;
    }
    
    /**
     * Busca os atendimentos a partir do número da senha
     * @param Novosga\Context $context
     */
    public function buscar(Context $context) {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = $context->request()->get('numero');
            $ab = new AtendimentoBusiness($this->em());
            $atendimentos = $ab->buscaAtendimentos($unidade, $numero);
            $response->data['total'] = sizeof($atendimentos);
            foreach ($atendimentos as $atendimento) {
                $response->data['atendimentos'][] = $atendimento->toArray();
            }
            $response->success = true;
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        return $response;
    }
    
    /**
     * Transfere o atendimento para outro serviço e prioridade
     * @param Novosga\Context $context
     */
    public function transferir(Context $context) {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->request()->post('id');
                /*
                 * TODO: verificar e tratar erro para ids invalidos. E verificar 
                 * se o servico informado esta disponivel para a unidade.
                 */
                $servico = (int) $context->request()->post('servico');
                $prioridade = (int) $context->request()->post('prioridade');
                $conn = $this->em()->getConnection();
                // transfere apenas se a data fim for nula (nao finalizados)
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        servico_id = :servico,
                        prioridade_id = :prioridade
                    WHERE 
                        id = :id AND 
                        unidade_id = :unidade AND
                        dt_fim IS NULL
                ");
                $stmt->bindValue('servico', $servico);
                $stmt->bindValue('prioridade', $prioridade);
                $stmt->bindValue('id', $id);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        return $response;
    }
    
    /**
     * Reativa o atendimento para o mesmo serviço e mesma prioridade.
     * Só pode reativar atendimentos que foram: Cancelados ou Não Compareceu
     * @param Novosga\Context $context
     */
    public function reativar(Context $context) {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->request()->post('id');
                $conn = $this->em()->getConnection();
                $status = join(',', array(AtendimentoBusiness::SENHA_CANCELADA, AtendimentoBusiness::NAO_COMPARECEU));
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
                $stmt->bindValue('status', AtendimentoBusiness::SENHA_EMITIDA);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        return $response;
    }
    
    /**
     * Atualiza o status da senha para cancelado
     * @param Novosga\Context $context
     */
    public function cancelar(Context $context) {
        $response = new JsonResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->request()->post('id');
                $conn = $this->em()->getConnection();
                // cancela apenas se a data fim for nula
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        status = :status,
                        dt_fim = :data
                    WHERE 
                        id = :id AND 
                        unidade_id = :unidade AND
                        dt_fim IS NULL
                ");
                $stmt->bindValue('status', AtendimentoBusiness::SENHA_CANCELADA);
                $stmt->bindValue('data', DateUtil::nowSQL());
                $stmt->bindValue('id', $id);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        return $response;
    }
    
}
