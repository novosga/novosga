<?php
namespace modules\sga\triagem;

use \PDO;
use \Exception;
use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use core\util\DateUtil;
use \core\model\Atendimento;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;

/**
 * TriagemController
 *
 * @author rogeriolino
 */
class TriagemController extends ModuleController {
      
    private function servicos(\core\model\Unidade $unidade) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.status = 1 ORDER BY e.nome");
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }

    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
        if ($unidade) {
            $this->view()->assign('servicos', $this->servicos($unidade));
        }
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Prioridade e WHERE e.status = 1 ORDER BY e.nome");
        $this->view()->assign('prioridades', $query->getResult());
    }

    /**
     * Versao do index do Triagem para monitores touchscreen
     * @param \core\SGAContext $context
     */
    public function touchscreen(SGAContext $context) {
        $this->index($context);
        $context->getResponse()->setRenderView(false);
    }
    
    public function imprimir(SGAContext $context) {
        $id = (int) Arrays::value($_GET, 'id');
        $atendimento = $this->em()->find("\core\model\Atendimento", $id);
        if (!$atendimento) {
            SGA::close();
        }
        $context->getResponse()->setRenderView(false);
        $this->view()->assign('atendimento', $atendimento);
    }
    
    public function ajax_update(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            $ids = Arrays::value($_GET, 'ids');
            $ids = Arrays::valuesToInt(explode(',', $ids));
            if (sizeof($ids)) {
                $conn = $this->em()->getConnection();
                $sql = "
                    SELECT 
                        id_serv as id, COUNT(*) as total 
                    FROM 
                        atendimentos
                    WHERE 
                        id_uni = :unidade AND 
                        id_serv IN (" . implode(',', $ids) . ")
                ";
                // total senhas do servico (qualquer status)
                $stmt = $conn->prepare($sql . " GROUP BY id_serv");
                $stmt->bindValue('unidade', $unidade->getId(), \PDO::PARAM_INT);
                $stmt->execute();
                $rs = $stmt->fetchAll();
                foreach ($rs as $r) {
                    $response->data[$r['id']] = array('total' => $r['total'], 'fila' => 0);
                }
                // total senhas esperando
                $stmt = $conn->prepare($sql . " AND id_stat = :status GROUP BY id_serv");
                $stmt->bindValue('unidade', $unidade->getId(), \PDO::PARAM_INT);
                $stmt->bindValue('status', Atendimento::SENHA_EMITIDA, \PDO::PARAM_INT);
                $stmt->execute();
                $rs = $stmt->fetchAll();
                foreach ($rs as $r) {
                    $response->data[$r['id']]['fila'] = $r['total'];
                }
                $response->success = true;
            }
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function servico_info(SGAContext $context) {
        $response = new AjaxResponse();
        if ($context->getRequest()->isPost()) {
            $id = (int) $context->getRequest()->getParameter('id');
            $servico = $this->em()->find("\core\model\Servico", $id);
            if ($servico) {
                $response->data['nome'] = $servico->getNome();
                $response->data['descricao'] = $servico->getDescricao();
                $response->data['subservicos'] = array();
                $query = $this->em()->createQuery("SELECT e FROM \core\model\Servico e WHERE e.mestre = :mestre ORDER BY e.nome");
                $query->setParameter('mestre', $servico->getId());
                $subservicos = $query->getResult();
                foreach ($subservicos as $s) {
                    $response->data['subservicos'][] = $s->getNome();
                }
            }
            $response->success = true;
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    public function distribui_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        $usuario = $context->getUser();
        try {
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            if (!$usuario) {
                throw new Exception(_('Nenhum usuário na sessão'));
            }
            // verificando a prioridade
            $prioridade = (int) Arrays::value($_POST, 'prioridade');
            $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM \core\model\Prioridade e WHERE e.id = :id");
            $query->setParameter('id', $prioridade);
            $rs = $query->getSingleResult();
            if ($rs['total'] == 0) {
                throw new Exception(_('Prioridade inválida'));
            }
            
            // verificando se o servico esta disponivel na unidade
            $servico = (int) Arrays::value($_POST, 'servico');
            $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('unidade', $unidade->getId());
            $query->setParameter('servico', $servico);
            $su = $query->getOneOrNullResult();
            if (!$su) {
                throw new Exception(_('Serviço não disponível para a unidade atual'));
            }
            $conn = $this->em()->getConnection();
            // ultimo numero gerado (total)
            $innerQuery = "SELECT num_senha FROM atendimentos a WHERE a.id_uni = :id_uni ORDER BY num_senha DESC";
            $innerQuery = $conn->getDatabasePlatform()->modifyLimitQuery($innerQuery, 1, 0);
            // ultimo numero gerado (servico). busca pela sigla do servico para nao aparecer duplicada (em caso de mais de um servico com a mesma sigla)
            $innerQuery2 = "SELECT num_senha_serv FROM atendimentos a WHERE a.id_uni = :id_uni AND a.sigla_senha = :sigla_senha ORDER BY num_senha_serv DESC";
            $innerQuery2 = $conn->getDatabasePlatform()->modifyLimitQuery($innerQuery2, 1, 0);
            $stmt = $conn->prepare(" 
                INSERT INTO atendimentos
                (id_uni, id_serv, id_pri, id_usu_tri, id_stat, nm_cli, ident_cli, num_guiche, dt_cheg, sigla_senha, num_senha, num_senha_serv)
                -- select dentro do insert para garantir atomicidade
                SELECT
                    :id_uni, :id_serv, :id_pri, :id_usu_tri, :id_stat, :nm_cli, :ident_cli, :num_guiche, :dt_cheg, :sigla_senha, 
                    COALESCE(
                        (
                            $innerQuery
                        ) , 0) + 1,
                    COALESCE(
                        (
                            $innerQuery2
                        ) , 0) + 1
            ");
            $stmt->bindValue('id_uni', $unidade->getId(), PDO::PARAM_INT);
            $stmt->bindValue('id_serv', $servico, PDO::PARAM_INT);
            $stmt->bindValue('id_pri', $prioridade, PDO::PARAM_INT);
            $stmt->bindValue('id_usu_tri', $usuario->getId(), PDO::PARAM_INT);
            $stmt->bindValue('id_stat', \core\model\Atendimento::SENHA_EMITIDA, PDO::PARAM_INT);
            $stmt->bindValue('nm_cli', Arrays::value($_POST, 'cli_nome', ''), PDO::PARAM_STR);
            $stmt->bindValue('ident_cli', Arrays::value($_POST, 'cli_doc', ''), PDO::PARAM_STR);
            $stmt->bindValue('num_guiche', 0, PDO::PARAM_INT);
            $stmt->bindValue('dt_cheg', DateUtil::nowSQL(), PDO::PARAM_STR);
            $stmt->bindValue('sigla_senha', $su->getSigla(), PDO::PARAM_STR);
            
            $response->success = ($stmt->execute() == true);
            if (!$response->success) {
                throw new Exception(_('Erro ao tentar gerar nova senha'));
            }
            $id = $conn->lastInsertId('atendimentos_id_atend_seq');
            if (!$id) {
                throw new \Exception(_('Erro ao pegar o ID gerado pelo banco. Entre em contato com a equipe de desenvolvimento informando esse problema, e o banco de dados que está usando'));
            }
            $response->data['id'] = $id;
            $response->data['atendimento'] = $this->em()->find("\core\model\Atendimento", $id)->toArray();
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
