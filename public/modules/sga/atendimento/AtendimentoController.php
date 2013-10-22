<?php
namespace modules\sga\atendimento;

use \Exception;
use \Novosga\SGA;
use \Novosga\SGAContext;
use \Novosga\Util\Arrays;
use \Novosga\Util\DateUtil;
use \Novosga\Business\AtendimentoBusiness;
use \Novosga\Controller\ModuleController;
use \Novosga\Model\Atendimento;
use \Novosga\Model\Util\UsuarioSessao;
use \Novosga\Http\AjaxResponse;
use \Novosga\Model\Modulo;

/**
 * AtendimentoController
 *
 * @author rogeriolino
 */
class AtendimentoController extends ModuleController {
    
    private $_atendimentoAtual;
    private $atendimentoBusiness;
    
    public function __construct(SGA $app, Modulo $modulo) {
        parent::__construct($app, $modulo);
        $this->atendimentoBusiness = new AtendimentoBusiness($this->em());
    }
    
    public function index(SGAContext $context) {
        $usuario = $context->getUser();
        $unidade = $context->getUnidade();
        if (!$usuario || !$unidade) {
            $this->app()->gotoHome();
        }
        $this->app()->view()->assign('time', time() * 1000);
        $this->app()->view()->assign('unidade', $unidade);
        $this->app()->view()->assign('atendimento', $this->atendimentoAndamento($usuario));
        $this->app()->view()->assign('servicos', $usuario->getServicos());
        $this->app()->view()->assign('servicosIndisponiveis', $usuario->getServicosIndisponiveis());
        $tiposAtendimento = array(
            UsuarioSessao::ATEND_TODOS => _('Todos'), 
            UsuarioSessao::ATEND_CONVENCIONAL => _('Convencional'), 
            UsuarioSessao::ATEND_PRIORIDADE => _('Prioridade')
        );
        $this->app()->view()->assign('tiposAtendimento', $tiposAtendimento);
        $this->app()->view()->assign('labelTipoAtendimento', $tiposAtendimento[$usuario->getTipoAtendimento()]);
        $this->app()->view()->assign('local', $usuario->getLocal());
        $this->app()->view()->assign('localCookie', $context->cookie()->get('local'));
        $this->app()->view()->assign('tipoAtendimentoCookie', $context->cookie()->get('tipo'));
    }
    
    public function set_local(SGAContext $context) {
        $numero = (int) Arrays::value($_POST, 'local');
        $tipo = (int) Arrays::value($_POST, 'tipo');
        if ($numero) {
            $context->cookie()->set('local', $numero);
            $context->cookie()->set('tipo', $tipo);
            $context->getUser()->setLocal($numero);
            $context->getUser()->setTipoAtendimento($tipo);
            $context->setUser($context->getUser());
        }
        $this->app()->redirect('index');
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
                Novosga\Model\Atendimento e 
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
        $query->setParameter('status', AtendimentoBusiness::SENHA_EMITIDA);
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
                AtendimentoBusiness::CHAMADO_PELA_MESA,
                AtendimentoBusiness::ATENDIMENTO_INICIADO,
                AtendimentoBusiness::ATENDIMENTO_ENCERRADO
            );
            $query = $this->em()->createQuery("SELECT e FROM Novosga\Model\Atendimento e WHERE e.usuario = :usuario AND e.status IN (:status)");
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
                // minimal data
                $response->data[] = $atendimento->toArray(true);
            }
            $response->success = true;
        }
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Chama ou rechama o próximo da fila
     * @param Novosga\SGAContext $context
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
            $this->app()->redirect('/home');
        }
        // verifica se ja esta atendendo alguem
        $atual = $this->atendimentoAndamento($usuario);
        // se ja existe um atendimento em andamento (chamando senha novamente)
        if ($atual) {
            $success = true;
            $proximo = $atual;
        } else {
            do {
                $query = $this->atendimentosQuery($usuario);
                $query->setMaxResults(1);
                $proximo = $query->getOneOrNullResult();
                if ($proximo) {
                    $proximo->setUsuario($context->getUser()->getWrapped());
                    $proximo->setLocal($context->getUser()->getLocal());
                    $proximo->setStatus(AtendimentoBusiness::CHAMADO_PELA_MESA);
                    $proximo->setDataChamada(new \DateTime());
                    // atualiza o proximo da fila
                    $query = $this->em()->createQuery("
                        UPDATE 
                            Novosga\Model\Atendimento e 
                        SET 
                            e.usuario = :usuario, e.local = :local, e.status = :novoStatus, e.dataChamada = :data
                        WHERE 
                            e.id = :id AND e.status = :statusAtual
                    ");
                    $query->setParameter('usuario', $proximo->getUsuario()->getId());
                    $query->setParameter('local', $proximo->getLocal());
                    $query->setParameter('novoStatus', $proximo->getStatus());
                    $query->setParameter('data', $proximo->getDataChamada());
                    $query->setParameter('id', $proximo->getId());
                    $query->setParameter('statusAtual', AtendimentoBusiness::SENHA_EMITIDA);
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
            $this->atendimentoBusiness->chamarSenha($unidade, $proximo);
            $response->data = $proximo->toArray();
        } else {
            if (!$proximo) {
                $response->message = _('Fila vazia');
            } else {
                $response->message = _('Já existe um atendimento em andamento');
            }
        }
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Muda o status do atendimento atual
     * @param mixed $statusAtual (array[int] | int)
     * @param int $novoStatus
     * @param string $campoData
     */
    private function mudaStatusAtualResponse(SGAContext $context, $statusAtual, $novoStatus, $campoData) {
        $usuario = $context->getUser();
        if (!$usuario) {
            $this->app()->gotoHome();
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
        $context->response()->jsonResponse($response);
    }
    
    /**
     * 
     * @param Novosga\Model\Atendimento $atendimento
     * @param mixed $statusAtual (array[int] | int)
     * @param int $novoStatus
     * @param string $campoData
     * @return boolean
     */
    private function mudaStatusAtendimento(Atendimento $atendimento, $statusAtual, $novoStatus, $campoData) {
        $cond = '';
        if ($campoData !== null) {
            $cond = ", e.$campoData = :data";
        }
        if (!is_array($statusAtual)) {
            $statusAtual = array($statusAtual);
        }
        // atualizando atendimento
        $query = $this->em()->createQuery("
            UPDATE 
                Novosga\Model\Atendimento e 
            SET 
                e.status = :novoStatus $cond
            WHERE 
                e.id = :id AND 
                e.status IN (:statusAtual)
        ");
        if ($campoData !== null) {
            $query->setParameter('data', DateUtil::nowSQL());
        }
        $query->setParameter('novoStatus', $novoStatus);
        $query->setParameter('id', $atendimento->getId());
        $query->setParameter('statusAtual', $statusAtual);
        return $query->execute() > 0;
    }
    
    /**
     * Inicia o atendimento com o proximo da fila
     * @param Novosga\SGAContext $context
     */
    public function iniciar(SGAContext $context) {
        $this->mudaStatusAtualResponse($context, AtendimentoBusiness::CHAMADO_PELA_MESA, AtendimentoBusiness::ATENDIMENTO_INICIADO, 'dataInicio');
    }
    
    /**
     * Marca o atendimento como nao compareceu
     * @param Novosga\SGAContext $context
     */
    public function nao_compareceu(SGAContext $context) {
        $this->mudaStatusAtualResponse($context, AtendimentoBusiness::CHAMADO_PELA_MESA, AtendimentoBusiness::NAO_COMPARECEU, 'dataFim');
    }
    
    /**
     * Marca o atendimento como encerrado
     * @param Novosga\SGAContext $context
     */
    public function encerrar(SGAContext $context) {
        $this->mudaStatusAtualResponse($context, AtendimentoBusiness::ATENDIMENTO_INICIADO, AtendimentoBusiness::ATENDIMENTO_ENCERRADO, null);
    }
    
    /**
     * Marca o atendimento como encerrado e codificado
     * @param Novosga\SGAContext $context
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
            $servicos = $context->request()->getParameter('servicos');
            $servicos = Arrays::valuesToInt(explode(',', $servicos));
            if (empty($servicos)) {
                $response->message = _('Nenhum serviço selecionado');
            } else {
                $conn = $this->em()->getConnection();
                $conn->beginTransaction();
                $stmt = $conn->prepare("INSERT INTO atend_codif (atendimento_id, servico_id, valor_peso) VALUES (:atendimento, :servico, 1)");
                foreach ($servicos as $s) {
                    $stmt->bindValue('atendimento', $atual->getId());
                    // TODO: verificar se o usuario realmente pode atender o servico informado
                    $stmt->bindValue('servico', $s);
                    $stmt->execute();
                }
                // verifica se esta encerrando e redirecionando
                $redirecionar = $context->request()->getParameter('redirecionar');
                if ($redirecionar) {
                    $servico = $context->request()->getParameter('novoServico');
                    $redirecionado = $this->redireciona_atendimento($atual, $servico, $unidade, $usuario);
                    if (!$redirecionado) {
                        throw new Exception(sprintf(_('Erro ao redirecionar atendimento %s para o serviço %s'), $atual->getId(), $servico));
                    }
                }
                $response->success = $this->mudaStatusAtendimento($atual, AtendimentoBusiness::ATENDIMENTO_ENCERRADO, AtendimentoBusiness::ATENDIMENTO_ENCERRADO_CODIFICADO, 'dataFim');
                if (!$response->success) {
                    throw new Exception(sprintf(_('Erro ao codificar o atendimento %s'), $atual->getId()));
                }
                $conn->commit();
            }
        } catch (Exception $e) {
            if ($conn && $conn->isTransactionActive()) {
                $conn->rollBack();
            }
            $response->message = $e->getMessage() . '<br><br><br>' . $e->getTraceAsString();
        }
        $context->response()->jsonResponse($response);
    }
    
    /**
     * Marca o atendimento como erro de triagem. E gera um novo atendimento para
     * o servico informado.
     * @param Novosga\SGAContext $context
     */
    public function redirecionar(SGAContext $context) {
        $unidade = $context->getUnidade();
        $response = new AjaxResponse(false);
        try {
            if (!$unidade) {
                throw new Exception(_('Nenhum unidade escolhida'));
            }
            $usuario = $context->getUser();
            $servico = (int) $context->request()->getParameter('servico');
            $atual = $this->atendimentoAndamento($usuario);
            if (!$atual) {
                throw new Exception(_('Nenhum atendimento em andamento'));
            }
            $conn = $this->em()->getConnection();
            $conn->beginTransaction();
            $redirecionado = $this->redireciona_atendimento($atual, $servico, $unidade, $usuario);
            if (!$redirecionado) {
                throw new Exception(sprintf(_('Erro ao redirecionar atendimento %s para o serviço %s'), $atual->getId(), $servico));
            }
            $response->success = $this->mudaStatusAtendimento($atual, array(AtendimentoBusiness::ATENDIMENTO_INICIADO, AtendimentoBusiness::ATENDIMENTO_ENCERRADO), AtendimentoBusiness::ERRO_TRIAGEM, 'dataFim');
            if (!$response->success) {
                throw new Exception(sprintf(_('Erro ao mudar status do atendimento %s para encerrado'), $atual->getId()));
            }
            $conn->commit();
        } catch (Exception $e) {
            if ($conn && $conn->isTransactionActive()) {
                $conn->rollBack();
            }
            $response->message = $e->getMessage() . '<br><br><br>' . $e->getTraceAsString();
        }
        $context->response()->jsonResponse($response);
    }
    
    private function redireciona_atendimento(Atendimento $atendimento, $servico, $unidade, UsuarioSessao $usuario) {
        // copiando a senha do atendimento atual
        // XXX: usando statement INSERT devido a bug do dblib (mssql) no linux com mapeamentos do Doctrine 
        $stmt = $this->em()->getConnection()->prepare("
            INSERT INTO atendimentos 
                (num_local, dt_cheg, status, sigla_senha, num_senha, num_senha_serv, servico_id, unidade_id, usuario_id, usuario_tri_id, prioridade_id, atendimento_id) 
            VALUES 
                (0, :data, :status, :sigla, :numero, :numero_servico, :servico, :unidade, :usuario, :usuario_triagem, :prioridade, :pai)
        ");
        // mudando a data de chegada para a data do redirecionamento
        $stmt->bindValue('data', DateUtil::nowSQL());
        $stmt->bindValue('status', AtendimentoBusiness::SENHA_EMITIDA);
        $stmt->bindValue('sigla', $atendimento->getSenha()->getSigla());
        $stmt->bindValue('numero', $atendimento->getNumeroSenha());
        $stmt->bindValue('numero_servico', $atendimento->getNumeroSenhaServico());
        $stmt->bindValue('servico', $servico);
        $stmt->bindValue('unidade', $unidade->getId());
        $stmt->bindValue('usuario', $usuario->getWrapped()->getId());
        $stmt->bindValue('usuario_triagem', $usuario->getWrapped()->getId());
        $stmt->bindValue('prioridade', $atendimento->getSenha()->getPrioridade()->getId());
        $stmt->bindValue('pai', $atendimento->getId());
        return $stmt->execute();
    }
    
    public function info_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $id = (int) $context->request()->getParameter('id');
            $atendimento = $this->atendimentoBusiness->buscaAtendimento($unidade, $id);
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
    public function consulta_senha(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUser()->getUnidade();
        if ($unidade) {
            $numero = $context->request()->getParameter('numero');
            $atendimentos = $this->atendimentoBusiness->buscaAtendimentos($unidade, $numero);
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
