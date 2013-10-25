<?php
namespace modules\sga\monitor;

use \Novosga\SGAContext;
use \Novosga\Util\Arrays;
use \Novosga\Util\DateUtil;
use \Novosga\Model\Unidade;
use \Novosga\Http\AjaxResponse;
use \Novosga\Controller\ModuleController;
use \Novosga\Business\AtendimentoBusiness;

/**
 * MonitorController
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MonitorController extends ModuleController {

    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->app()->view()->assign('unidade', $unidade);
        if ($unidade) {
            // servicos
            $this->app()->view()->assign('servicos', $this->servicos($unidade));
        }
        // lista de prioridades para ser utilizada ao redirecionar senha
        $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Prioridade e WHERE e.status = 1 ORDER BY e.peso, e.nome");
        $this->app()->view()->assign('prioridades', $query->getResult());
        $this->app()->view()->assign('milis', time() * 1000);
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
    
    public function ajax_update(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            $ids = Arrays::value($_GET, 'ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (sizeof($ids)) {
                $response->data['total'] = 0;
                $servicos = $this->servicos($unidade, " e.servico IN (" . implode(',', $ids) . ") ");
                $em = $context->database()->createEntityManager();
                for ($i = 0; $i < sizeof($servicos); $i++) {
                    $su = $servicos[$i];
                    $total = $su->getFila($em)->size();
                    // prevent overhead
                    if ($total) {
                        $fila = array();
                        for ($j = 0; $j < $total; $j++) {
                            $atendimento = $su->getFila($em)->get($j); 
                            $arr = $atendimento->toArray(true);
                            $fila[] = $arr;
                        }
                        $response->data['servicos'][$su->getServico()->getId()] = $fila;
                        $response->data['total']++;
                    }
                }
                $response->success = true;
            }
        }
        $context->response()->jsonResponse($response);
    }
    
    public function info_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $id = (int) $context->request()->getParameter('id');
            $ab = new AtendimentoBusiness($this->em());
            $atendimento = $ab->buscaAtendimento($unidade, $id);
            if ($atendimento) {
                $response->data = $atendimento->toArray();
                $response->success = true;
            } else {
                $response->message = _('Atendimento inválido');
            }
        }
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Busca os atendimentos a partir do número da senha
     * @param Novosga\SGAContext $context
     */
    public function buscar(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = $context->request()->getParameter('numero');
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
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Transfere o atendimento para outro serviço e prioridade
     * @param Novosga\SGAContext $context
     */
    public function transferir(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->request()->getParameter('id');
                /*
                 * TODO: verificar e tratar erro para ids invalidos. E verificar 
                 * se o servico informado esta disponivel para a unidade.
                 */
                $servico = (int) $context->request()->getParameter('servico');
                $prioridade = (int) $context->request()->getParameter('prioridade');
                $conn = $this->em()->getConnection();
                // transfere apenas se a data fim for nula (nao finalizados)
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        servico_id = :servico,
                        prioridade_id = :prioridade
                    WHERE 
                        atendimento_id = :id AND 
                        unidade_id = :unidade AND
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
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Reativa o atendimento para o mesmo serviço e mesma prioridade.
     * Só pode reativar atendimentos que foram: Cancelados ou Não Compareceu
     * @param Novosga\SGAContext $context
     */
    public function reativar(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->request()->getParameter('id');
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
                        atendimento_id = :id AND 
                        unidade_id = :unidade AND
                        status IN ({$status})
                ");
                $stmt->bindValue('id', $id);
                $stmt->bindValue('status', AtendimentoBusiness::SENHA_EMITIDA);
                $stmt->bindValue('unidade', $unidade->getId());
                $response->success = $stmt->execute() > 0;
            } catch (\Exception $e) {
                $response->message = $e->getMessage();
            }
        } else{
            $response->message = _('Nenhuma unidade selecionada');
        }
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Atualiza o status da senha para cancelado
     * @param Novosga\SGAContext $context
     */
    public function cancelar(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            try {
                $id = (int) $context->request()->getParameter('id');
                $conn = $this->em()->getConnection();
                // cancela apenas se a data fim for nula
                $stmt = $conn->prepare("
                    UPDATE 
                        atendimentos
                    SET 
                        status = :status,
                        dt_fim = :data
                    WHERE 
                        atendimento_id = :id AND 
                        unidade_id = :unidade AND
                        dt_fim IS NULL
                ");
                $stmt->bindValue('status', AtendimentoBusiness::SENHA_CANCELADA);
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
        $context->response()->jsonResponse($response);
    }
    
}
