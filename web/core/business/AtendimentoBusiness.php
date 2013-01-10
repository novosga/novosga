<?php
namespace core\business;

use \PDO;
use \Exception;
use \core\util\DateUtil;
use \core\db\DB;

/**
 * AtendimentoBusiness
 *
 * @author rogeriolino
 */
abstract class AtendimentoBusiness {

    /**
     * Move os registros da tabela atendimento para a tabela de historico de atendimentos.
     * Se a unidade não for informada, será acumulado serviços de todas as unidades.
     * @param type $unidade
     * @throws Exception
     */
    public static function acumularAtendimentos($unidade = 0) {
        if ($unidade instanceof \core\model\Unidade) {
            $unidade = $unidade->getId();
        }
        try {
            $em = DB::getEntityManager();
            $conn = $em->getConnection();
            $data = DateUtil::nowSQL();
            $conn->beginTransaction();
            // salva atendimentos da unidade
            $sql = "
                INSERT INTO historico_atendimentos
                SELECT 
                    a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.num_senha, 
                    a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli
                FROM 
                    atendimentos a
                WHERE 
                    a.dt_cheg <= :data
            ";
            if ($unidade > 0) {
                $sql .= " AND a.id_uni = :unidade";
            }
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            // salva atendimentos codificados da unidade
            $subquery = "SELECT a.id_atend FROM atendimentos a WHERE dt_cheg <= :data ";
            if ($unidade > 0) {
                $subquery .= " AND a.id_uni = :unidade";
            }
            $query = $conn->prepare("
                INSERT INTO historico_atend_codif
                SELECT 
                    ac.id_atend, ac.id_serv, ac.valor_peso
                FROM 
                    atend_codif ac
                WHERE 
                    id_atend IN (
                        $subquery
                    )
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            // limpa atendimentos codificados da unidade
            $subquery = "SELECT id_atend FROM atendimentos a WHERE a.dt_cheg <= :data ";
            if ($unidade > 0) {
                $subquery .= " AND a.id_uni = :unidade";
            }
            $query = $conn->prepare("
                DELETE FROM 
                    atend_codif ac
                WHERE 
                    ac.id_atend IN (
                        $subquery
                    )
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            // limpa atendimentos da unidade
            $sql = "DELETE FROM atendimentos a WHERE dt_cheg <= :data ";
            if ($unidade > 0) {
                $sql .= " AND a.id_uni = :unidade";
            }
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            $conn->commit();
        } catch (Exception $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw new Exception($e->getMessage());
        }
    }
    
}
