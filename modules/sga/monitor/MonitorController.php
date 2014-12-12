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
        try {
            $unidade = $context->getUser()->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
<<<<<<< HEAD
            $id = (int) $context->request()->post('id');
            $atendimento = $this->em()->find('Novosga\Model\Atendimento', $id);
            if (!$atendimento || $atendimento->getServicoUnidade()->getUnidade()->getId() != $unidade->getId()) {
                throw new Exception(_('Atendimento inválido'));
            }
            if (!$atendimento) {
                throw new Exception(_('Atendimento inválido'));
            }
            /*
             * TODO: verificar se o servico informado esta disponivel para a unidade.
             */
            $servico = (int) $context->request()->post('servico');
            $prioridade = (int) $context->request()->post('prioridade');
            
            $ab = new AtendimentoBusiness($this->em());
            $response->success = $ab->transferir($atendimento, $servico, $prioridade);
=======
            
            $id = (int) $context->request()->post('id');
            $atendimento = $this->em()->find('Novosga\Model\Atendimento', $id);
            if (!$atendimento) {
                throw new Exception(_('Atendimento inválido'));
            }
            
            $servicoId = (int) $context->request()->post('servico');
            $servico = $this->em()->find('Novosga\Model\Servico', $servicoId);
            if (!$servico) {
                throw new Exception(_('Serviço inválido'));
            }
            
            $prioridadeId = (int) $context->request()->post('prioridade');
            $prioridade = $this->em()->find('Novosga\Model\Prioridade', $prioridadeId);
            if (!$prioridade) {
                throw new Exception(_('Prioridade inválida'));
            }

            $ab = new AtendimentoBusiness($this->em());
            $response->success = $ab->transferir($atendimento, $unidade, $servico, $prioridade);
>>>>>>> 4a2149a92fea790e08da7dd2b65b8cfd0af5b930
        } catch (Exception $e) {
            $response->message = $e->getMessage();
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
<<<<<<< HEAD
        try {
            $unidade = $context->getUser()->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
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
=======
        $unidade = $context->getUser()->getUnidade();
        try {
            if (!$unidade) {
                $response->message = _('Nenhuma unidade selecionada');
            }
            
            $id = (int) $context->request()->post('id');
            $atendimento = $this->em()->find('Novosga\Model\Atendimento', $id);
            if (!$atendimento) {
                throw new Exception(_('Atendimento inválido'));
            }
            
            $ab = new AtendimentoBusiness($this->em());
            $response->success = $ab->reativar($atendimento, $unidade);
>>>>>>> 4a2149a92fea790e08da7dd2b65b8cfd0af5b930
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
    /**
     * Atualiza o status da senha para cancelado
     * @param Novosga\Context $context
     */
    public function cancelar(Context $context) {
        $response = new JsonResponse();
<<<<<<< HEAD
        try {
            $unidade = $context->getUser()->getUnidade();
            if (!$unidade) {
                throw new Exception(_('Nenhuma unidade selecionada'));
            }
            $id = (int) $context->request()->post('id');
            
            $ab = new AtendimentoBusiness($this->em());
            $response->success = $ab->cancelar($atendimento, $servico, $prioridade);
=======
        $unidade = $context->getUser()->getUnidade();
        try {
            if (!$unidade) {
                $response->message = _('Nenhuma unidade selecionada');
            }
            
            $id = (int) $context->request()->post('id');
            $atendimento = $this->em()->find('Novosga\Model\Atendimento', $id);
            if (!$atendimento) {
                throw new Exception(_('Atendimento inválido'));
            }
            
            $ab = new AtendimentoBusiness($this->em());
            $response->success = $ab->cancelar($atendimento, $unidade);
>>>>>>> 4a2149a92fea790e08da7dd2b65b8cfd0af5b930
        } catch (Exception $e) {
            $response->message = $e->getMessage();
        }
        return $response;
    }
    
}
