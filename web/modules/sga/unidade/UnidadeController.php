<?php
namespace modules\sga\unidade;

use \PDO;
use \Exception;
use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use \core\util\DateUtil;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;

/**
 * UnidadeController
 * 
 * Controlador do módulo de configuração da unidade
 *
 * @author rogeriolino
 */
class UnidadeController extends ModuleController {
    
    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
        if ($unidade) {
            // atualizando relacionamento entre unidade e servicos
            $conn = $this->em()->getConnection();
            $conn->executeUpdate("
                INSERT INTO uni_serv 
                SELECT :unidade, id_serv, 1, nm_serv, '', 0 FROM servicos 
                WHERE 
                    id_macro IS NULL AND
                    id_serv NOT IN (SELECT id_serv FROM uni_serv WHERE id_uni = :unidade)
            ", array('unidade' => $unidade->getId()));
            // todos servicos mestre
            $query = $this->em()->createQuery("SELECT e FROM \core\model\ServicoUnidade e WHERE e.unidade = :unidade ORDER BY e.nome");
            $query->setParameter('unidade', $unidade->getId());
            $this->view()->assign('servicos', $query->getResult());
        }
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
        $response = new AjaxResponse();
        $response->success = true;
        $sigla = Arrays::value($_POST, 'sigla');
        $id_serv = Arrays::value($_POST, 'id');
        $id_uni = $context->getUser()->getUnidade()->getId();
        $query = $this->em()->createQuery("UPDATE \core\model\ServicoUnidade e SET e.sigla = :sigla WHERE e.unidade = :unidade AND e.servico = :servico");
        $query->setParameter('sigla', $sigla);
        $query->setParameter('servico', $id_serv);
        $query->setParameter('unidade', $id_uni);
        $query->execute();
        $context->getResponse()->jsonResponse($response);
    }
    
    public function acumular_atendimentos(SGAContext $context) {
        $response = new AjaxResponse();
        $unidade = $context->getUnidade();
        if ($unidade) {
            $conn = $this->em()->getConnection();
            try {
                $data = DateUtil::nowSQL();
                $conn->beginTransaction();
                // salva atendimentos da unidade
                $query = $conn->prepare("
                    INSERT INTO historico_atendimentos
                    SELECT 
                        a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.num_senha, 
                        a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli
                    FROM 
                        atendimentos a
                    WHERE 
                        a.dt_cheg <= :data AND 
                        a.id_uni = :unidade
                ");
                $query->bindValue('data', $data, PDO::PARAM_STR);
                $query->bindValue('unidade', $unidade->getId(), PDO::PARAM_INT);
                $query->execute();

                // salva atendimentos codificados da unidade
                $query = $conn->prepare("
                    INSERT INTO historico_atend_codif
                    SELECT 
                        ac.id_atend, ac.id_serv, ac.valor_peso
                    FROM 
                        atend_codif ac
                    WHERE 
                        id_atend IN (
                            SELECT a.id_atend FROM atendimentos a WHERE dt_cheg <= :data AND a.id_uni = :unidade
                        )
                ");
                $query->bindValue('data', $data, PDO::PARAM_STR);
                $query->bindValue('unidade', $unidade->getId(), PDO::PARAM_INT);
                $query->execute();

                // limpa atendimentos codificados da unidade
                $query = $conn->prepare("
                    DELETE FROM 
                        atend_codif ac
                    WHERE 
                        ac.id_atend IN (
                            SELECT id_atend FROM atendimentos a WHERE a.dt_cheg <= :data AND a.id_uni = :unidade
                        )
                ");
                $query->bindValue('data', $data, PDO::PARAM_STR);
                $query->bindValue('unidade', $unidade->getId(), PDO::PARAM_INT);
                $query->execute();

                // limpa atendimentos da unidade
                $query = $conn->prepare("DELETE FROM atendimentos a WHERE dt_cheg <= :data AND a.id_uni = :unidade");
                $query->bindValue('data', $data, PDO::PARAM_STR);
                $query->bindValue('unidade', $unidade->getId(), PDO::PARAM_INT);
                $query->execute();

                $conn->commit();
                $response->success = true;
            } catch (Exception $e) {
                if ($conn->isTransactionActive()) {
                    $conn->rollBack();
                }
                $response->message = $e->getMessage();
            }
        } else {
            $response->message = _('Nenhum unidade definida');
        }
        $context->getResponse()->jsonResponse($response);
    }
    
}
