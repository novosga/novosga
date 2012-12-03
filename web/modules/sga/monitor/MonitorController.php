<?php
namespace modules\sga\monitor;

use \core\SGAContext;
use \core\util\Arrays;
use \core\model\Unidade;
use \core\model\util\Monitor;
use \core\controller\ModuleController;

/**
 * MonitorController
 *
 * @author rogeriolino
 */
class MonitorController extends ModuleController {
    
    public function __construct() {
        $this->title = _('Monitor');
        $this->subtitle = _('Gerencie as senhas aguardando atendimento');
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

    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
        if ($unidade) {
            // servicos
            $this->view()->assign('servicos', $this->servicos($unidade));
        }
    }
    
    public function ajax_update(SGAContext $context) {
        $response = array('success' => false, 'servicos' => array());
        $unidade = $context->getUnidade();
        if ($unidade) {
            $ids = Arrays::value($_GET, 'ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (sizeof($ids)) {
                $response['total'] = 0;
                $servicos = $this->servicos($unidade, " e.servico IN (" . implode(',', $ids) . ") ");
                for ($i = 0; $i < sizeof($servicos); $i++) {
                    $su = $servicos[$i];
                    $total = $su->getFila()->size();
                    // prevent overhead
                    if ($total) {
                        $fila = array();
                        for ($j = 0; $j < $total; $j++) {
                            $senha = $su->getFila()->get($j)->getSenha(); 
                            $item = array(
                                'numero' => $senha->getNumero(), 
                                'numero_full' => $senha->toString(), 
                                'prioridade' => $senha->isPrioridade()
                            );
                            if ($senha->isPrioridade()) {
                                $item['nomePrioridade'] = $senha->getPrioridade()->getNome();
                            }
                            $fila[] = $item;
                        }
                        $response['servicos'][$su->getServico()->getId()] = $fila;
                        $response['total']++;
                    }
                }
                $response['success'] = true;
            }
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    private function buscaAtendimentos(Unidade $unidade, $numeroSenha) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Atendimento e JOIN e.servicoUnidade su WHERE e.numeroSenha = :numero AND su.unidade = :unidade ORDER BY e.id");
        $query->setParameter('numero', (int) $numeroSenha);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }
    
    public function info_senha(SGAContext $context) {
        $response = array('success' => false);
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = Arrays::value($_POST, 'numero');
            $atendimentos = $this->buscaAtendimentos($unidade, $numero);
            if (sizeof($atendimentos)) {
                // vendo ultimo atendimento para essa senha
                $atendimento = end($atendimentos);
                $response['numero'] = $atendimento->getSenha()->toString();
                $response['prioridade'] = $atendimento->getSenha()->isPrioridade() ? $atendimento->getSenha()->getPrioridade()->getNome() : _('Atendimento Normal');
                $response['cli_nome'] = $atendimento->getCliente()->getNome();
                $response['cli_doc'] = $atendimento->getCliente()->getDocumento();
                $response['dt_cheg'] = $atendimento->getDataChegada();
                $response['success'] = true;
            }
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
