<?php
namespace modules\sga\monitor;

use \core\SGAContext;
use \core\util\Arrays;
use \core\util\DateUtil;
use \core\model\Unidade;
use \core\model\Atendimento;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;

/**
 * MonitorController
 *
 * @author rogeriolino
 */
class MonitorController extends ModuleController {

    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
        if ($unidade) {
            // servicos
            $this->view()->assign('servicos', $this->servicos($unidade));
        }
        // lista de prioridades para ser utilizada ao redirecionar senha
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Prioridade e WHERE e.status = 1 ORDER BY e.peso, e.nome");
        $this->view()->assign('prioridades', $query->getResult());
        $this->view()->assign('situacoes', \core\model\Atendimento::situacoes());
    }
    
    private function servicos(Unidade $unidade, $where = "") {
        $dql = "SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade ";
        if (!empty($where)) {
            $dql .= " AND $where";
        }
        $dql .= " ORDER BY e.nome";
        $query = $this->em()->createQuery($dql);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }
    
    public function ajax_update(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            $ids = Arrays::value($_GET, 'ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (sizeof($ids)) {
                $response->data['total'] = 0;
                $servicos = $this->servicos($unidade, " e.servico IN (" . implode(',', $ids) . ") ");
                for ($i = 0; $i < sizeof($servicos); $i++) {
                    $su = $servicos[$i];
                    $total = $su->getFila()->size();
                    // prevent overhead
                    if ($total) {
                        $fila = array();
                        for ($j = 0; $j < $total; $j++) {
                            $atendimento = $su->getFila()->get($j); 
                            $fila[] = $atendimento->toArray(true);
                        }
                        $response->data['servicos'][$su->getServico()->getId()] = $fila;
                        $response->data['total']++;
                    }
                }
                $response->success = true;
            }
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    private function buscaAtendimento(Unidade $unidade, $id) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Atendimento e JOIN e.servicoUnidade su WHERE e.id = :id AND su.unidade = :unidade");
        $query->setParameter('id', (int) $id);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getOneOrNullResult();
    }
    
    private function buscaAtendimentos(Unidade $unidade, $numeroSenha) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Atendimento e JOIN e.servicoUnidade su WHERE e.numeroSenha = :numero AND su.unidade = :unidade ORDER BY e.id");
        $query->setParameter('numero', (int) $numeroSenha);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }
    
    public function info_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $id = (int) $context->getRequest()->getParameter('id');
            $atendimento = $this->buscaAtendimento($unidade, $id);
            if ($atendimento) {
                $response->data = $atendimento->toArray();
                $response->success = true;
            } else {
                $response->message = _('Atendimento inválido');
            }
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Busca os atendimentos a partir do número da senha
     * @param \core\SGAContext $context
     */
    public function buscar(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = (int) $context->getRequest()->getParameter('numero');
            $atendimentos = $this->buscaAtendimentos($unidade, $numero);
            $response->data['total'] = sizeof($atendimentos);
            foreach ($atendimentos as $atendimento) {
                $response->data['atendimentos'][] = $atendimento->toArray();
            }
            $response->success = true;
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Transfere o atendimento para outro serviço e prioridade
     * @param \core\SGAContext $context
     */
    public function transferir(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->getRequest()->getParameter('id');
                /*
                 * TODO: verificar e tratar erro para ids invalidos. E verificar 
                 * se o servico informado esta disponivel para a unidade.
                 */
                $servico = (int) $context->getRequest()->getParameter('servico');
                $prioridade = (int) $context->getRequest()->getParameter('prioridade');
                $conn = $this->em()->getConnection();
                // transfere apenas se a data fim for nula (nao finalizados)
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        id_serv = :servico,
                        id_pri = :prioridade
                    WHERE 
                        id_atend = :id AND 
                        id_uni = :unidade AND
                        dt_fim IS NULL
                        
                ");
                $stmt->bindValue('servico', $servico);
                $stmt->bindValue('prioridade', $prioridade);
                $stmt->bindValue('id', $id);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (\Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Reativa o atendimento para o mesmo serviço e mesma prioridade
     * @param \core\SGAContext $context
     */
    public function reativar(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->getRequest()->getParameter('id');
                $conn = $this->em()->getConnection();
                // reativa apenas se estiver finalizada (data fim diferente de nulo)
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        id_stat = :status,
                        dt_fim = NULL
                    WHERE 
                        id_atend = :id AND 
                        id_uni = :unidade AND
                        dt_fim IS NOT NULL
                        
                ");
                $stmt->bindValue('id', $id);
                $stmt->bindValue('status', Atendimento::SENHA_EMITIDA);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (\Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Atualiza o status da senha para cancelado
     * @param \core\SGAContext $context
     */
    public function cancelar(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->getRequest()->getParameter('id');
                $conn = $this->em()->getConnection();
                // cancela apenas se a data fim for nula
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        id_stat = :status,
                        dt_fim = :data
                    WHERE 
                        id_atend = :id AND 
                        id_uni = :unidade AND
                        dt_fim IS NULL
                ");
                $stmt->bindValue('status', \core\model\Atendimento::SENHA_CANCELADA);
                $stmt->bindValue('data', DateUtil::nowSQL());
                $stmt->bindValue('id', $id);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (\Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
