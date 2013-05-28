<?php
namespace modules\sga\atendimento;

use \Exception;
use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use core\util\DateUtil;
use \core\business\AtendimentoBusiness;
use \core\controller\ModuleController;
use \core\model\Atendimento;
use \core\model\util\UsuarioSessao;
use \core\http\AjaxResponse;

/**
 * AtendimentoController
 *
 * @author rogeriolino
 */
class AtendimentoController extends ModuleController {
    
    private $_atendimentoAtual;
    
    public function index(SGAContext $context) {
        $usuario = $context->getUser();
        $unidade = $context->getUnidade();
        if (!$usuario || !$unidade) {
            SGA::redirect('/' . SGA::K_HOME);
        }
        $this->view()->assign('unidade', $unidade);
        $this->view()->assign('atendimento', $this->atendimentoAndamento($usuario));
        $this->view()->assign('servicos', $usuario->getServicos());
        $this->view()->assign('servicosIndisponiveis', $usuario->getServicosIndisponiveis());
        $tiposAtendimento = array(
            UsuarioSessao::ATEND_TODOS => _('Todos'), 
            UsuarioSessao::ATEND_CONVENCIONAL => _('Convencional'), 
            UsuarioSessao::ATEND_PRIORIDADE => _('Prioridade')
        );
        $this->view()->assign('tiposAtendimento', $tiposAtendimento);
    }
    
    public function set_guiche(SGAContext $context) {
        $numero = (int) Arrays::value($_POST, 'guiche');
        $tipo = (int) Arrays::value($_POST, 'tipo');
        if ($numero) {
            $context->getCookie()->set('guiche', $numero);
            $context->getCookie()->set('tipo', $tipo);
            $context->getUser()->setGuiche($numero);
            $context->getUser()->setTipoAtendimento($tipo);
            $context->setUser($context->getUser());
        }
        SGA::redirect('index');
    }
    
    private function atendimentosQuery(UsuarioSessao $usuario) {
        $ids = array();
        $servicos = $usuario->getServicos();
        foreach ($servicos as $s) {
            $ids[] = $s->getServico()->getId();
        }
        // se nao tiver servicos, coloca id invalido so para nao dar erro no sql
        if (empty($ids)) {
            $ids[] = 0;
        }
        $cond = '';
        if ($usuario->getTipoAtendimento() != UsuarioSessao::ATEND_TODOS) {
            $s = ($usuario->getTipoAtendimento() == UsuarioSessao::ATEND_CONVENCIONAL) ? '=' : '>';
            $cond = " AND p.peso $s 0";
        }
        $query = $this->em()->createQuery("
            SELECT 
                e 
            FROM 
                \core\model\Atendimento e 
                JOIN e.prioridadeSenha p
                JOIN e.servicoUnidade su 
                JOIN su.servico s 
            WHERE 
                e.status = :status AND
                su.unidade = :unidade AND
                s.id IN (:servicos) $cond
            ORDER BY 
                p.peso DESC,
                e.numeroSenha ASC
        ");
        $query->setParameter('status', Atendimento::SENHA_EMITIDA);
        $query->setParameter('unidade', $usuario->getUnidade()->getId());
        $query->setParameter('servicos', $ids);
        return $query;
    }
    
    private function atendimentos(UsuarioSessao $usuario) {
        return $this->atendimentosQuery($usuario)->getResult();
    }
    
    private function atendimentoAndamento(UsuarioSessao $usuario) {
        if (!$this->_atendimentoAtual) {
            $status = array(
                Atendimento::CHAMADO_PELA_MESA,
                Atendimento::ATENDIMENTO_INICIADO,
                Atendimento::ATENDIMENTO_ENCERRADO
            );
            $query = $this->em()->createQuery("SELECT e FROM \core\model\Atendimento e WHERE e.usuario = :usuario AND e.status IN (:status)");
            $query->setParameter('usuario', $usuario->getId());
            $query->setParameter('status', $status);
            $this->_atendimentoAtual = $query->getOneOrNullResult();
        }
        return $this->_atendimentoAtual;
    }
    
    public function get_fila(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            // fila de atendimento do atendente atual
            $response->data = array();
            $atendimentos = $this->atendimentos($context->getUser());
            foreach ($atendimentos as $atendimento) {
                $response->data[] = $atendimento->toArray(true);
            }
            $response->success = true;
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Chama ou rechama o próximo da fila
     * @param \core\SGAContext $context
     */
    public function chamar(SGAContext $context) {
        $attempts = 0;
        $maxAttempts = 5;
        $proximo = null;
        $success = false;
        $response = new AjaxResponse();
        $usuario = $context->getUser();
        $unidade = $context->getUnidade();
        if (!$usuario) {
            SGA::redirect('/' . SGA::K_HOME);
        }
        // verifica se ja esta atendendo alguem
        $atual = $this->atendimentoAndamento($usuario);
        if ($atual) {
            // se ja existe um atendimento em andamento, exibe mensagem de erro
            if ($atual->getStatus() == Atendimento::ATENDIMENTO_INICIADO) {
                $success = false;
            } 
            // chamando senha novamente
            else {
                $success = true;
                $proximo = $atual;
            }
        } else {
            do {
                $query = $this->atendimentosQuery($usuario);
                $query->setMaxResults(1);
                $proximo = $query->getOneOrNullResult();
                if ($proximo) {
                    $proximo->setUsuario($context->getUser()->getWrapped());
                    $proximo->setGuiche($context->getUser()->getGuiche());
                    $proximo->setStatus(Atendimento::CHAMADO_PELA_MESA);
                    $proximo->setDataChamada(DateUtil::nowSQL());
                    // atualiza o proximo da fila
                    $query = $this->em()->createQuery("
                        UPDATE 
                            \core\model\Atendimento e 
                        SET 
                            e.usuario = :usuario, e.guiche = :guiche, e.status = :novoStatus, e.dataChamada = :data
                        WHERE 
                            e.id = :id AND e.status = :statusAtual
                    ");
                    $query->setParameter('usuario', $proximo->getUsuario()->getId());
                    $query->setParameter('guiche', $proximo->getGuiche());
                    $query->setParameter('novoStatus', $proximo->getStatus());
                    $query->setParameter('data', $proximo->getDataChamada());
                    $query->setParameter('id', $proximo->getId());
                    $query->setParameter('statusAtual', Atendimento::SENHA_EMITIDA);
                    /* 
                     * caso entre o intervalo do select e o update, o proximo ja tiver sido chamado
                     * a consulta retornara 0, entao tenta pegar o proximo novamente (outro)
                     */
                    $success = $query->execute() > 0;
                    $attempts++;
                } else {
                    // nao existe proximo
                    break;
                }
            } while (!$success && $attempts < $maxAttempts);
        }
        // response
        $response->success = $success;
        if ($success) {
            AtendimentoBusiness::chamarSenha($unidade, $proximo);
            $response->data = $proximo->toArray();
        } else {
            if (!$proximo) {
                $response->message = _('Fila vazia');
            } else {
                $response->message = _('Já existe um atendimento em andamento');
            }
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Muda o status do atendimento atual
     * @param type $statusAtual
     * @param type $novoStatus
     * @param type $campoData
     */
    private function mudaStatusAtualResponse(SGAContext $context, $statusAtual, $novoStatus, $campoData) {
        $usuario = $context->getUser();
        if (!$usuario) {
            SGA::redirect('/' . SGA::K_HOME);
        }
        $response = new AjaxResponse();
        $atual = $this->atendimentoAndamento($usuario);
        if ($atual) {
            // atualizando atendimento
            $response->success = $this->mudaStatusAtendimento($atual, $statusAtual, $novoStatus, $campoData);
        }
        if ($response->success) {
            $response->data = $atual->toArray();
        } else {
            $response->message = _('Nenhum atendimento disponível');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    private function mudaStatusAtendimento(Atendimento $atendimento, $statusAtual, $novoStatus, $campoData) {
        // atualizando atendimento
        $query = $this->em()->createQuery("
            UPDATE 
                \core\model\Atendimento e 
            SET 
                e.$campoData = :data, e.status = :novoStatus
            WHERE 
                e.id = :id AND 
                e.status IN (:statusAtual)
        ");
        if (!is_array($statusAtual)) {
            $statusAtual = array($statusAtual);
        }
        $query->setParameter('data', DateUtil::nowSQL());
        $query->setParameter('novoStatus', $novoStatus);
        $query->setParameter('id', $atendimento->getId());
        $query->setParameter('statusAtual', $statusAtual);
        return $query->execute() > 0;
    }
    
    /**
     * Inicia o atendimento com o proximo da fila
     * @param \core\SGAContext $context
     */
    public function iniciar(SGAContext $context) {
        $this->mudaStatusAtualResponse($context, Atendimento::CHAMADO_PELA_MESA, Atendimento::ATENDIMENTO_INICIADO, 'dataInicio');
    }
    
    /**
     * Marca o atendimento como nao compareceu
     * @param \core\SGAContext $context
     */
    public function nao_compareceu(SGAContext $context) {
        $this->mudaStatusAtualResponse($context, Atendimento::CHAMADO_PELA_MESA, Atendimento::NAO_COMPARECEU, 'dataFim');
    }
    
    /**
     * Marca o atendimento como encerrado
     * @param \core\SGAContext $context
     */
    public function encerrar(SGAContext $context) {
        $this->mudaStatusAtualResponse($context, Atendimento::ATENDIMENTO_INICIADO, Atendimento::ATENDIMENTO_ENCERRADO, 'dataFim');
    }
    
    /**
     * Marca o atendimento como encerrado e codificado
     * @param \core\SGAContext $context
     */
    public function codificar(SGAContext $context) {
        $unidade = $context->getUnidade();
        $response = new AjaxResponse(false);
        try {
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $usuario = $context->getUser();
            $atual = $this->atendimentoAndamento($usuario);
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $servicos = $context->getRequest()->getParameter('servicos');
            $servicos = Arrays::valuesToInt(explode(',', $servicos));
            if (empty($servicos)) {
                $response->message = _('Nenhum serviço selecionado');
            } else {
                $conn = $this->em()->getConnection();
                $conn->beginTransaction();
                $stmt = $conn->prepare("INSERT INTO atend_codif (id_atend, id_serv, valor_peso) VALUES (:atendimento, :servico, 1)");
                foreach ($servicos as $s) {
                    $stmt->bindValue('atendimento', $atual->getId());
                    // TODO: verificar se o usuario realmente pode atender o servico informado
                    $stmt->bindValue('servico', $s);
                    $stmt->execute();
                }
                // verifica se esta encerrando e redirecionando
                $redirecionar = $context->getRequest()->getParameter('redirecionar');
                if ($redirecionar) {
                    $servico = $context->getRequest()->getParameter('novoServico');
                    $this->redireciona_atendimento($atual, $servico, $unidade, $usuario);
                }
                $response->success = $this->mudaStatusAtendimento($atual, Atendimento::ATENDIMENTO_ENCERRADO, Atendimento::ATENDIMENTO_ENCERRADO_CODIFICADO, 'dataFim');
                $conn->commit();
            }
        } catch (\Exception $e) {
            if ($conn && $conn->isTransactionActive()) {
                $conn->rollBack();
            }
            $response->message = $e->getMessage() . '<br><br><br>' . $e->getTraceAsString();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Marca o atendimento como erro de triagem. E gera um novo atendimento para
     * o servico informado.
     * @param \core\SGAContext $context
     */
    public function redirecionar(SGAContext $context) {
        $unidade = $context->getUnidade();
        $response = new AjaxResponse(false);
        try {
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $usuario = $context->getUser();
            $servico = (int) $context->getRequest()->getParameter('servico');
            $atual = $this->atendimentoAndamento($usuario);
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $conn = $this->em()->getConnection();
            $conn->beginTransaction();
            $this->redireciona_atendimento($atual, $servico, $unidade, $usuario);
            $response->success = $this->mudaStatusAtendimento($atual, array(Atendimento::ATENDIMENTO_INICIADO, Atendimento::ATENDIMENTO_ENCERRADO), Atendimento::ERRO_TRIAGEM, 'dataFim');
            $conn->commit();
        } catch (Exception $e) {
            if ($conn && $conn->isTransactionActive()) {
                $conn->rollBack();
            }
            $response->message = $e->getTraceAsString();
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    private function redireciona_atendimento($atendimento, $servico, $unidade, UsuarioSessao $usuario) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.servico = :servico AND e.unidade = :unidade");
        $query->setParameter('servico', $servico);
        $query->setParameter('unidade', $unidade->getId());
        $servicoUnidade = $query->getOneOrNullResult();
        if (!$servicoUnidade) {
            throw new Exception(_('Serviço inválido'));
        }
        // copiando a senha do atendimento atual
        // XXX: usando statement INSERT devido a bug do dblib (mssql) no linux com mapeamentos do Doctrine 
        $stmt = $this->em()->getConnection()->prepare("
            INSERT INTO atendimentos 
                (num_guiche, dt_cheg, id_stat, sigla_senha, num_senha, num_senha_serv, id_serv, id_uni, id_usu, id_usu_tri, id_pri) 
            VALUES 
                (0, :data, :status, :sigla, :numero, :numero_servico, :servico, :unidade, :usuario, :usuario_triagem, :prioridade)
        ");
        $stmt->bindValue('data', $atendimento->getDataChegada());
        $stmt->bindValue('status', Atendimento::SENHA_EMITIDA);
        $stmt->bindValue('sigla', $atendimento->getSenha()->getSigla());
        $stmt->bindValue('numero', $atendimento->getNumeroSenha());
        $stmt->bindValue('numero_servico', $atendimento->getNumeroSenhaServico());
        $stmt->bindValue('servico', $servico);
        $stmt->bindValue('unidade', $unidade->getId());
        $stmt->bindValue('usuario', $usuario->getWrapped()->getId());
        $stmt->bindValue('usuario_triagem', $usuario->getWrapped()->getId());
        $stmt->bindValue('prioridade', $atendimento->getSenha()->getPrioridade()->getId());
        $stmt->execute();
    }
    
}
