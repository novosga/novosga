<?php
namespace modules\sga\atendimento;

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use core\util\DateUtil;
use \core\controller\ModuleController;
use \core\model\Atendimento;
use \core\model\Unidade;
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
    }
    
    public function set_guiche(SGAContext $context) {
        $numero = (int) Arrays::value($_POST, 'guiche');
        if ($numero) {
            $context->getCookie()->set('guiche', $numero);
            $context->getUser()->setGuiche($numero);
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
                s.id IN (:servicos)
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
            $query = $this->em()->createQuery("SELECT e FROM \core\model\Atendimento e WHERE e.usuario = :usuario AND (e.status = :status1 OR e.status = :status2)");
            $query->setParameter('usuario', $usuario->getId());
            $query->setParameter('status1', Atendimento::CHAMADO_PELA_MESA);
            $query->setParameter('status2', Atendimento::ATENDIMENTO_INICIADO);
            $this->_atendimentoAtual = $query->getOneOrNullResult();
        }
        return $this->_atendimentoAtual;
    }
    
    public function get_fila(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            // fila de atendimento do atendente atual
            $rs = $this->atendimentos($context->getUser());
            $response->data = array();
            foreach ($rs as $a) {
                $atendimento = array(
                    'numero' => $a->getSenha()->toString(),
                    'prioridade' => $a->getSenha()->isPrioridade(),
                    'servico' => $a->getServicoUnidade()->getNome()
                );
                if ($atendimento['prioridade']) {
                    $atendimento['nomePrioridade'] = $a->getSenha()->getPrioridade()->getNome();
                }
                $response->data[] = $atendimento;
            }
            $response->success = true;
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    private function chamaSenha(Unidade $unidade, Atendimento $atendimento) {
        $conn = $this->em()->getConnection();
    	$stmt = $conn->prepare("
            INSERT INTO painel_senha 
            (id_uni, id_serv, num_senha, sig_senha, msg_senha, nm_local, num_guiche) 
            VALUES 
            (:id_uni, :id_serv, :num_senha, :sig_senha, :msg_senha, :nm_local, :num_guiche)
        ");
        $stmt->bindValue('id_uni', $unidade->getId());
        $stmt->bindValue('id_serv', $atendimento->getServicoUnidade()->getServico()->getId());
        $stmt->bindValue('num_senha', $atendimento->getSenha()->getNumero());
        $stmt->bindValue('sig_senha', $atendimento->getSenha()->getSigla());
        $stmt->bindValue('msg_senha', $atendimento->getSenha()->getLegenda());
        $stmt->bindValue('nm_local', _('Guichê')); // TODO: pegar o nome do local de atendimento (guiche, sala, etc)
        $stmt->bindValue('num_guiche', $atendimento->getGuiche());
        $stmt->execute();
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
            $this->chamaSenha($unidade, $proximo);
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
    private function mudaStatusAtual(SGAContext $context, $statusAtual, $novoStatus, $campoData) {
        $usuario = $context->getUser();
        if (!$usuario) {
            SGA::redirect('/' . SGA::K_HOME);
        }
        $response = new AjaxResponse();
        $atual = $this->atendimentoAndamento($usuario);
        if ($atual) {
            // atualizando atendimento
            $query = $this->em()->createQuery("
                UPDATE 
                    \core\model\Atendimento e 
                SET 
                    e.$campoData = :data, e.status = :novoStatus
                WHERE 
                    e.id = :id AND e.status = :statusAtual
            ");
            $query->setParameter('data', DateUtil::nowSQL());
            $query->setParameter('novoStatus', $novoStatus);
            $query->setParameter('id', $atual->getId());
            $query->setParameter('statusAtual', $statusAtual);
            $response->success = $query->execute() > 0;
        }
        if ($response->success) {
            $response->data = $atual->toArray();
        } else {
            $response->message = _('Nenhum atendimento disponível');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
    /**
     * Inicia o atendimento com o proximo da fila
     * @param \core\SGAContext $context
     */
    public function iniciar(SGAContext $context) {
        $this->mudaStatusAtual($context, Atendimento::CHAMADO_PELA_MESA, Atendimento::ATENDIMENTO_INICIADO, 'dataInicio');
    }
    
    /**
     * Marca o atendimento como nao compareceu
     * @param \core\SGAContext $context
     */
    public function nao_compareceu(SGAContext $context) {
        $this->mudaStatusAtual($context, Atendimento::CHAMADO_PELA_MESA, Atendimento::NAO_COMPARECEU, 'dataFim');
    }
    
    /**
     * Marca o atendimento como nao compareceu
     * @param \core\SGAContext $context
     */
    public function encerrar(SGAContext $context) {
        $atual = $this->atendimentoAndamento($context->getUser());
        if ($atual) {
            $servicos = $context->getRequest()->getParameter('servicos');
            $servicos = Arrays::valuesToInt(explode(',', $servicos));
            if (empty($servicos)) {
                $response = new AjaxResponse(false, _('Nenhum serviço selecionado'));
                $context->getResponse()->jsonResponse($response);
            } else {
                $conn = $this->em()->getConnection();
                $stmt = $conn->prepare("INSERT INTO atend_codif (id_atend, id_serv, valor_peso) VALUES (:atendimento, :servico, 1)");
                foreach ($servicos as $s) {
                    $stmt->bindValue('atendimento', $atual->getId());
                    // TODO: verificar se o usuario realmente pode atender o servico informado
                    $stmt->bindValue('servico', $s);
                    $stmt->execute();
                }
                $this->mudaStatusAtual($context, Atendimento::ATENDIMENTO_INICIADO, Atendimento::ATENDIMENTO_ENCERRADO, 'dataFim');
            }
        } else {
            $response = new AjaxResponse(false, _('Nenhum atendimento em andamento'));
            $context->getResponse()->jsonResponse($response);
        }
    }
    
    /**
     * Marca o atendimento como erro de triagem
     * @param \core\SGAContext $context
     */
    public function erro_triagem(SGAContext $context) {
        $this->mudaStatusAtual($context, Atendimento::ATENDIMENTO_INICIADO, Atendimento::ERRO_TRIAGEM, 'dataFim');
    }
    
}
