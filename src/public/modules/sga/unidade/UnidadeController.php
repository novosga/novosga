<?php
namespace modules\sga\unidade;

use \novosga\SGA;
use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\http\AjaxResponse;
use \novosga\controller\ModuleController;
use \novosga\business\PainelBusiness;
use \novosga\business\AtendimentoBusiness;

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
        $this->app()->view()->assign('unidade', $unidade);
        if ($unidade) {
            /*
             * XXX: Os parametros abaixo (id da unidade e sigla) estao sendo concatenados direto na string devido a um bug do pdo_sqlsrv (windows)
             */
            // atualizando relacionamento entre unidade e servicos
            $conn = $this->em()->getConnection();
            $conn->executeUpdate("
                INSERT INTO uni_serv 
                SELECT 
                    {$unidade->getId()}, id, 1, nome, 'A', 0 FROM servicos 
                WHERE 
                    id_macro IS NULL AND
                    id NOT IN (SELECT servico_id FROM uni_serv WHERE unidade_id = :unidade)
            ", array('unidade' => $unidade->getId()));
            // todos servicos mestre
            $query = $this->em()->createQuery("
                SELECT 
                    e 
                FROM 
                    novosga\model\ServicoUnidade e 
                WHERE 
                    e.unidade = :unidade 
                ORDER BY 
                    e.nome
            ");
            $query->setParameter('unidade', $unidade->getId());
            $this->app()->view()->assign('servicos', $query->getResult());
            $this->app()->view()->assign('paineis', PainelBusiness::paineis($unidade));
            $query = $this->em()->createQuery("SELECT e FROM novosga\model\Local e ORDER BY e.nome");
            $this->app()->view()->assign('locais', $query->getResult());
        }
    }
    
    public function painel_info(SGAContext $context) {
        $response = new AjaxResponse();
        try {
            $unidade = $context->getUnidade();
            $host = (int) $context->request()->getParameter('host');
            $response->data = PainelBusiness::painelInfo($unidade, $host);
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function update_impressao(SGAContext $context) {
        $impressao = (int) Arrays::value($_POST, 'impressao');
        $mensagem = Arrays::value($_POST, 'mensagem', '');
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $query = $this->em()->createQuery("UPDATE novosga\model\Unidade e SET e.statusImpressao = :status, e.mensagemImpressao = :mensagem WHERE e.id = :unidade");
            $query->setParameter('status', $impressao);
            $query->setParameter('mensagem', $mensagem);
            $query->setParameter('unidade', $unidade->getId());
            if ($query->execute()) {
                // atualizando sessao
                $unidade = $this->em()->find('novosga\model\Unidade', $unidade->getId());
                $context->setUnidade($unidade);
            }
        }
        echo (new AjaxResponse(true))->toJson();
        exit();
    }
    
    private function change_status(SGAContext $context, $status) {
        $servico_id = (int) Arrays::value($_POST, 'id');
        $unidade = $context->getUser()->getUnidade();
        if (!$servico_id || !$unidade) {
            return false;
        }
        $query = $this->em()->createQuery("UPDATE novosga\model\ServicoUnidade e SET e.status = :status WHERE e.unidade = :unidade AND e.servico = :servico");
        $query->setParameter('status', $status);
        $query->setParameter('servico', $servico_id);
        $query->setParameter('unidade', $unidade->getId());
        return $query->execute();
    }
    
    public function habilita_servico(SGAContext $context) {
        $response = new AjaxResponse();
        $response->success = $this->change_status($context, 1);
        $context->response()->jsonResponse($response);
    }
    
    public function desabilita_servico(SGAContext $context) {
        $response = new AjaxResponse();
        $response->success = $this->change_status($context, 0);
        $context->response()->jsonResponse($response);
    }
    
    public function update_servico(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->request()->getParameter('id');
        try {
            $query = $this->em()->createQuery("SELECT e FROM novosga\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('servico', $id);
            $query->setParameter('unidade', $context->getUser()->getUnidade()->getId());
            $su = $query->getSingleResult();

            $sigla = $context->request()->getParameter('sigla');
            $nome = $context->request()->getParameter('nome');
            $local = $this->em()->find("novosga\model\Local", (int) $context->request()->getParameter('local'));
            
            $su->setSigla($sigla);
            $su->setNome($nome);
            if ($local) {
                $su->setLocal($local);
            }
            $this->em()->merge($su);
            $this->em()->flush();
            $response->success = true;
        } catch (\Exception $e) {
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    public function reverte_nome(SGAContext $context) {
        $response = new AjaxResponse();
        $id = (int) $context->request()->getParameter('id');
        $servico = $this->em()->find('novosga\model\Servico', $id);
        if ($servico) {
            $query = $this->em()->createQuery("UPDATE novosga\model\ServicoUnidade e SET e.nome = :nome WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('nome', $servico->getNome());
            $query->setParameter('servico', $servico->getId());
            $query->setParameter('unidade', $context->getUser()->getUnidade()->getId());
            $query->execute();
            $response->data['nome'] = $servico->getNome();
            $response->success = true;
        } else {
            $response->message = _('Serviço inválido');
        }
        $context->response()->jsonResponse($response);
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
        $context->response()->jsonResponse($response);
    }
    
}
