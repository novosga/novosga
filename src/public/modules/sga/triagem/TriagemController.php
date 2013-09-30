<?php
namespace modules\sga\triagem;

use \PDO;
use \Exception;
use \novosga\SGAContext;
use \novosga\util\Arrays;
use \novosga\util\DateUtil;
use \novosga\model\Atendimento;
use \novosga\http\AjaxResponse;
use \novosga\controller\ModuleController;
use \novosga\business\AtendimentoBusiness;

/**
 * TriagemController
 *
 * @author rogeriolino
 */
class TriagemController extends ModuleController {

    
    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->app()->view()->assign('unidade', $unidade);
        if ($unidade) {
            $this->app()->view()->assign('servicos', $this->servicos($unidade));
        }
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\Prioridade e WHERE e.status = 1 ORDER BY e.nome");
        $this->app()->view()->assign('prioridades', $query->getResult());
    } 
    
    private function servicos(\novosga\model\Unidade $unidade) {
        $query = $this->em()->createQuery("SELECT e FROM novosga\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.status = 1 ORDER BY e.nome");
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }

    /**
     * Versao do index do Triagem para monitores touchscreen
     * @param novosga\SGAContext $context
     */
    public function touchscreen(SGAContext $context) {
        $this->index($context);
        $context->response()->setRenderView(false);
    }
    
    public function imprimir(SGAContext $context) {
        $id = (int) Arrays::value($_GET, 'id');
        $atendimento = $this->em()->find("novosga\model\Atendimento", $id);
        if (!$atendimento) {
            $this->app()->redirect('index');
        }
        $context->response()->setRenderView(false);
        $this->app()->view()->assign('atendimento', $atendimento);
        $this->app()->view()->assign('data', DateUtil::now("d/m/Y H:i"));
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
                        servico_id as id, COUNT(*) as total 
                    FROM 
                        atendimentos
                    WHERE 
                        unidade_id = :unidade AND 
                        servico_id IN (" . implode(',', $ids) . ")
                ";
                // total senhas do servico (qualquer status)
                $stmt = $conn->prepare($sql . " GROUP BY servico_id");
                $stmt->bindValue('unidade', $unidade->getId(), \PDO::PARAM_INT);
                $stmt->execute();
                $rs = $stmt->fetchAll();
                foreach ($rs as $r) {
                    $response->data[$r['id']] = array('total' => $r['total'], 'fila' => 0);
                }
                // total senhas esperando
                $stmt = $conn->prepare($sql . " AND status = :status GROUP BY servico_id");
                $stmt->bindValue('unidade', $unidade->getId(), \PDO::PARAM_INT);
                $stmt->bindValue('status', AtendimentoBusiness::SENHA_EMITIDA, \PDO::PARAM_INT);
                $stmt->execute();
                $rs = $stmt->fetchAll();
                foreach ($rs as $r) {
                    $response->data[$r['id']]['fila'] = $r['total'];
                }
                $response->success = true;
            }
        }
        $context->response()->jsonResponse($response);
    }
    
    public function servico_info(SGAContext $context) {
        $response = new AjaxResponse();
        if ($context->request()->isPost()) {
            $id = (int) $context->request()->getParameter('id');
            $servico = $this->em()->find("novosga\model\Servico", $id);
            if ($servico) {
                $response->data['nome'] = $servico->getNome();
                $response->data['descricao'] = $servico->getDescricao();
                // ultima senha
                $query = $this->em()->createQuery("SELECT e FROM novosga\model\Atendimento e JOIN e.servicoUnidade su WHERE su.servico = :servico AND su.unidade = :unidade ORDER BY e.numeroSenha DESC");
                $query->setParameter('servico', $servico->getId());
                $query->setParameter('unidade', $context->getUnidade()->getId());
                $atendimentos = $query->getResult();
                if (sizeof($atendimentos)) {
                    $response->data['senha'] = $atendimentos[0]->getSenha()->toString();
                } else {
                    $response->data['senha'] = '-';
                }
                // subservicos
                $response->data['subservicos'] = array();
                $query = $this->em()->createQuery("SELECT e FROM novosga\model\Servico e WHERE e.mestre = :mestre ORDER BY e.nome");
                $query->setParameter('mestre', $servico->getId());
                $subservicos = $query->getResult();
                foreach ($subservicos as $s) {
                    $response->data['subservicos'][] = $s->getNome();
                }
            }
            $response->success = true;
        }
        $context->response()->jsonResponse($response);
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
            $query = $this->em()->createQuery("SELECT COUNT(e) as total FROM novosga\model\Prioridade e WHERE e.id = :id");
            $query->setParameter('id', $prioridade);
            $rs = $query->getSingleResult();
            if ($rs['total'] == 0) {
                throw new Exception(_('Prioridade inválida'));
            }
            
            // verificando se o servico esta disponivel na unidade
            $servico = (int) Arrays::value($_POST, 'servico');
            $query = $this->em()->createQuery("SELECT e FROM novosga\model\ServicoUnidade e WHERE e.unidade = :unidade AND e.servico = :servico");
            $query->setParameter('unidade', $unidade->getId());
            $query->setParameter('servico', $servico);
            $su = $query->getOneOrNullResult();
            if (!$su) {
                throw new Exception(_('Serviço não disponível para a unidade atual'));
            }
            $conn = $this->em()->getConnection();
            /*
             * XXX: Os parametros abaixo (id da unidade e sigla) estao sendo concatenados direto na string devido a um bug do pdo_sqlsrv (windows)
             */
            // ultimo numero gerado (total)
            $innerQuery = "SELECT num_senha FROM atendimentos a WHERE a.unidade_id = {$unidade->getId()} ORDER BY num_senha DESC";
            $innerQuery = $conn->getDatabasePlatform()->modifyLimitQuery($innerQuery, 1, 0);
            // ultimo numero gerado (servico). busca pela sigla do servico para nao aparecer duplicada (em caso de mais de um servico com a mesma sigla)
            $innerQuery2 = "SELECT num_senha_serv FROM atendimentos a WHERE a.unidade_id = {$unidade->getId()} AND a.sigla_senha = '{$su->getSigla()}' ORDER BY num_senha_serv DESC";
            $innerQuery2 = $conn->getDatabasePlatform()->modifyLimitQuery($innerQuery2, 1, 0);
            $stmt = $conn->prepare(" 
                INSERT INTO atendimentos
                (unidade_id, servico_id, prioridade_id, usuario_tri_id, status, nm_cli, ident_cli, num_guiche, dt_cheg, sigla_senha, num_senha, num_senha_serv)
                SELECT
                    :unidade_id, :servico_id, :prioridade_id, :usuario_tri_id, :status, :nm_cli, :ident_cli, :num_guiche, :dt_cheg, :sigla_senha, 
                    COALESCE(
                        (
                            $innerQuery
                        ) , 0) + 1,
                    COALESCE(
                        (
                            $innerQuery2
                        ) , 0) + 1
            ");
            $stmt->bindValue('unidade_id', $unidade->getId(), PDO::PARAM_INT);
            $stmt->bindValue('servico_id', $servico, PDO::PARAM_INT);
            $stmt->bindValue('prioridade_id', $prioridade, PDO::PARAM_INT);
            $stmt->bindValue('usuario_tri_id', $usuario->getId(), PDO::PARAM_INT);
            $stmt->bindValue('status', AtendimentoBusiness::SENHA_EMITIDA, PDO::PARAM_INT);
            $stmt->bindValue('nm_cli', Arrays::value($_POST, 'cli_nome', ''), PDO::PARAM_STR);
            $stmt->bindValue('ident_cli', Arrays::value($_POST, 'cli_doc', ''), PDO::PARAM_STR);
            $stmt->bindValue('num_guiche', 0, PDO::PARAM_INT);
            $stmt->bindValue('dt_cheg', DateUtil::nowSQL(), PDO::PARAM_STR);
            $stmt->bindValue('sigla_senha', $su->getSigla(), PDO::PARAM_STR);
            
            $response->success = ($stmt->execute() == true);
            if (!$response->success) {
                throw new Exception(_('Erro ao tentar gerar nova senha'));
            }
            $id = $conn->lastInsertId();
            if (!$id) {
                $id = $conn->lastInsertId('atendimentos_id_seq');
            }
            if (!$id) {
                throw new \Exception(_('Erro ao pegar o ID gerado pelo banco. Entre em contato com a equipe de desenvolvimento informando esse problema, e o banco de dados que está usando'));
            }
            $response->data['id'] = $id;
            $response->data['atendimento'] = $this->em()->find("novosga\model\Atendimento", $id)->toArray();
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
        }
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Busca os atendimentos a partir do número da senha
     * @param novosga\SGAContext $context
     */
    public function consulta_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = $context->request()->getParameter('numero');
            $atendimentos = \novosga\business\AtendimentoBusiness::buscaAtendimentos($unidade, $numero);
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
    
}
