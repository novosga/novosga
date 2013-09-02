<?php
namespace modules\sga\unidade;

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;
use \core\business\PainelBusiness;
use \core\business\AtendimentoBusiness;

/**
 * UnidadeController
 * 
 * Controlador do módulo de configuração da unidade
 *
 * @author rogeriolino
 */
class UnidadeController extends ModuleController {
    
    public function index(SGAContext $context) {
        $unidade = $context->getUnidade();
        $this->view()->assign('unidade', $unidade);
        if ($unidade) {
            /*
             * XXX: Os parametros abaixo (id da unidade e sigla) estao sendo concatenados direto na string devido a um bug do pdo_sqlsrv (windows)
             */
            // atualizando relacionamento entre unidade e servicos
            $conn = $this->em()->getConnection();
            $conn->executeUpdate("
                INSERT INTO uni_serv 
                SELECT {$unidade->getId()}, id_serv, 1, nm_serv, 'A', 0 FROM servicos 
                WHERE 
                    id_macro IS NULL AND
                    id_serv NOT IN (SELECT id_serv FROM uni_serv WHERE id_uni = :unidade)
            ", array('unidade' => $unidade->getId()));
            // todos servicos mestre
            $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade ORDER BY e.nome");
            $query->setParameter('unidade', $unidade->getId());
            $this->view()->assign('servicos', $query->getResult());
            $this->view()->assign('paineis', PainelBusiness::paineis($unidade));
        }
    }
    
    public function painel_info(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $unidade = $context->getUnidade();
            $host = (int) $context->getRequest()->getParameter('host');
            $response->data = PainelBusiness::painelInfo($unidade, $host);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function update_impressao(SGAContext $context) {
        $impressao = (int) Arrays::value($_POST, 'impressao');
        $mensagem = Arrays::value($_POST, 'mensagem', '');
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $query = $this->em()->createQuery("UPDATE \core\model\Unidade e SET e.statusImpressao = :status, e.mensagemImpressao = :mensagem WHERE e.id = :unidade");
            $query->setParameter('status', $impressao);
            $query->setParameter('mensagem', $mensagem);
            $query->setParameter('unidade', $unidade->getId());
            if ($query->execute()) {
                // atualizando sessao
                $unidade = $this->em()->find('\core\model\Unidade', $unidade->getId());
                $context->setUnidade($unidade);
            }
        }
        SGA::redirect('index');
    }
    
    private function change_status(SGAContext $context, $status) {
        $id_serv = (int) Arrays::value($_POST, 'id');
        $unidade = $context->getUser()->getUnidade();
        if (!$id_serv || !$unidade) {
            return false;
        }
        $query = $this->em()->createQuery("UPDATE \core\model\ServicoUnidade e SET e.status = :status WHERE e.unidade = :unidade AND e.servico = :servico");
        $query->setParameter('status', $status);
        $query->setParameter('servico', $id_serv);
        $query->setParameter('unidade', $unidade->getId());
        return $query->execute();
    }
    
    public function habilita_servico(SGAContext $context) {
        $response = new AjaxResponse();
        $response->success = $this->change_status($context, 1);
        $context->getResponse()->jsonResponse($response);
    }
    
    public function desabilita_servico(SGAContext $context) {
        $response = new AjaxResponse();
        $response->success = $this->change_status($context, 0);
        $context->getResponse()->jsonResponse($response);
    }
    
    public function update_sigla(SGAContext $context) {
        $sigla = $context->getRequest()->getParameter('sigla');
        $this->update_field($context, 'sigla', strtoupper($sigla));
    }
    
    public function update_nome(SGAContext $context) {
        $value = $context->getRequest()->getParameter('nome');
        $this->update_field($context, 'nome', $value);
    }
    
    private function update_field(SGAContext $context, $field, $value) {
        $response = new AjaxResponse(true);
        $id = (int) $context->getRequest()->getParameter('id');
        $query = $this->em()->createQuery("UPDATE \core\model\ServicoUnidade e SET e.{$field} = :value WHERE e.unidade = :unidade AND e.servico = :servico");
        $query->setParameter('value', $value);
        $query->setParameter('servico', $id);
        $query->setParameter('unidade', $context->getUser()->getUnidade()->getId());
        $query->execute();
        $context->getResponse()->jsonResponse($response);
    }
    
    public function reverte_nome(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->getRequest()->getParameter('id');
        $servico = $this->em()->find('\core\model\Servico', $id);
        if ($servico) {
            $query = $this->em()->createQuery("UPDATE \core\model\ServicoUnidade e SET e.nome = :nome WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('nome', $servico->getNome());
            $query->setParameter('servico', $servico->getId());
            $query->setParameter('unidade', $context->getUser()->getUnidade()->getId());
            $query->execute();
            $response->data['nome'] = $servico->getNome();
            $response->success = true;
        } else {
            $response->message = _('Serviço inválido');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function acumular_atendimentos(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            try {
                AtendimentoBusiness::acumularAtendimentos($unidade);
                $response->success = true;
            } catch (\Exception $e) {
                $response->message = $e->getMessage();
            }
        } else {
            $response->message = _('Nenhum unidade definida');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
