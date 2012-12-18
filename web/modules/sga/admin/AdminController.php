<?php
namespace modules\sga\admin;

use \PDO;
use \Exception;
use \core\SGAContext;
use \core\util\DateUtil;
use \core\http\AjaxResponse;
use \core\controller\ModuleController;

/**
 * AdminView
 * @author rogeriolino
 */
class AdminController extends ModuleController {
    
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
