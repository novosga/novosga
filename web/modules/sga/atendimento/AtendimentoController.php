<?php
namespace modules\sga\atendimento;

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use core\util\DateUtil;
use \core\controller\ModuleController;
use \core\model\Atendimento;
use \core\model\Usuario;

/**
 * AtendimentoController
 *
 * @author rogeriolino
 */
class AtendimentoController extends ModuleController {
    
    public function index(SGAContext $context) {
        $usuario = $context->getUser();
        $unidade = $context->getUnidade();
        if (!$usuario || !$unidade) {
            SGA::redirect('/' . SGA::K_HOME);
        }
        $this->view()->assign('unidade', $unidade);
        $this->view()->assign('atendimento', $this->atendimentoAndamento($usuario));
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
    
    private function atendimentosQuery(Usuario $usuario) {
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
                su.unidade = :unidade 
            ORDER BY 
                p.peso DESC,
                e.numeroSenha ASC
        ");
        // TODO: pegar os servidos do usuario e jogar na query (AND s.id IN (:servicos))
        $query->setParameter('status', Atendimento::SENHA_EMITIDA);
        $query->setParameter('unidade', $usuario->getUnidade()->getId());
        //$query->setParameter('servicos', array(15));
        return $query;
    }
    
    private function atendimentos(Usuario $usuario) {
        return $this->atendimentosQuery($usuario)->getResult();
    }
    
    private function atendimentoAndamento(Usuario $usuario) {
        $query = $this->em()->createQuery("SELECT e FROM \core\model\Atendimento e WHERE e.usuario = :usuario AND (e.status = :status1 OR e.status = :status2)");
        $query->setParameter('usuario', $usuario->getId());
        $query->setParameter('status1', Atendimento::CHAMADO_PELA_MESA);
        $query->setParameter('status2', Atendimento::ATENDIMENTO_INICIADO);
        return $query->getOneOrNullResult();
    }
    
    public function get_fila(SGAContext $context) {
        $response = array('success' => false);
        $unidade = $context->getUnidade();
        if ($unidade) {
            // fila de atendimento do atendente atual
            $rs = $this->atendimentos($context->getUser());
            $response['atendimentos'] = array();
            foreach ($rs as $a) {
                $atendimento = array(
                    'numero' => $a->getSenha()->toString(),
                    'prioridade' => $a->getSenha()->isPrioridade(),
                    'servico' => $a->getServicoUnidade()->getNome()
                );
                if ($atendimento['prioridade']) {
                    $atendimento['nomePrioridade'] = $a->getSenha()->getPrioridade()->getNome();
                }
                $response['atendimentos'][] = $atendimento;
            }
            $response['success'] = true;
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
        $response = array();
        $usuario = $context->getUser();
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
            // TODO: 
        } else {
            do {
                $query = $this->atendimentosQuery($usuario);
                $query->setMaxResults(1);
                $proximo = $query->getOneOrNullResult();
                if ($proximo) {
                    // atualiza o proximo da fila
                    $query = $this->em()->createQuery("
                        UPDATE 
                            \core\model\Atendimento e 
                        SET 
                            e.usuario = :usuario, e.guiche = :guiche, e.status = :novoStatus, e.dataChamada = :data
                        WHERE 
                            e.id = :id AND e.status = :statusAtual
                    ");
                    $query->setParameter('usuario', $context->getUser()->getId());
                    $query->setParameter('guiche', $context->getUser()->getGuiche());
                    $query->setParameter('novoStatus', Atendimento::CHAMADO_PELA_MESA);
                    $query->setParameter('data', DateUtil::nowSQL());
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
        $response['success'] = $success;
        if ($success) {
            $response['atendimento'] = $proximo->toArray();
        } else {
            if (!$proximo) {
                $response['message'] = _('Fila vazia');
            } else {
                $response['message'] = _('Já existe um atendimento em andamento');
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
        $response = array('success' => false);
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
            $response['success'] = $query->execute() > 0;
        }
        if ($response['success']) {
            $response['atendimento'] = $atual->toArray();
        } else {
            $response['message'] = _('Nenhum atendimento disponível');
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
    public function naocompareceu(SGAContext $context) {
        $this->mudaStatusAtual($context, Atendimento::CHAMADO_PELA_MESA, Atendimento::NAO_COMPARECEU, 'dataFim');
    }
    
    /**
     * Marca o atendimento como nao compareceu
     * @param \core\SGAContext $context
     */
    public function encerrar(SGAContext $context) {
        $this->mudaStatusAtual($context, Atendimento::ATENDIMENTO_INICIADO, Atendimento::ATENDIMENTO_ENCERRADO, 'dataFim');
    }
    
    /**
     * Marca o atendimento como erro de triagem
     * @param \core\SGAContext $context
     */
    public function errotriagem(SGAContext $context) {
        $this->mudaStatusAtual($context, Atendimento::ATENDIMENTO_INICIADO, Atendimento::ERRO_TRIAGEM, 'dataFim');
    }
    
}
