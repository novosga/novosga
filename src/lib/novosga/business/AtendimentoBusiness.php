<?php
namespace novosga\business;

use \PDO;
use \Exception;
use \novosga\util\DateUtil;
use \novosga\model\Unidade;
use \novosga\model\Atendimento;
use \novosga\db\DB;

/**
 * AtendimentoBusiness
 *
 * @author rogeriolino
 */
abstract class AtendimentoBusiness {
    
    public static function chamarSenha(Unidade $unidade, Atendimento $atendimento) {
        $em = DB::getEntityManager();
        $conn = $em->getConnection();
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
     * Move os registros da tabela atendimento para a tabela de historico de atendimentos.
     * Se a unidade não for informada, será acumulado serviços de todas as unidades.
     * @param type $unidade
     * @throws Exception
     */
    public static function acumularAtendimentos($unidade = 0) {
        if ($unidade instanceof \novosga\model\Unidade) {
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
                (
                    id_atend, id_uni, id_usu, id_serv, id_pri, id_stat, sigla_senha, num_senha, num_senha_serv, 
                    nm_cli, num_guiche, dt_cheg, dt_cha, dt_ini, dt_fim, ident_cli, id_usu_tri
                )
                SELECT 
                    a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.sigla_senha, a.num_senha, a.num_senha_serv, 
                    a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli, a.id_usu_tri
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
            $sql = self::delFrom('atend_codif', 'ac') . "WHERE ac.id_atend IN ( $subquery )";
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            // limpa atendimentos da unidade
            $sql = self::delFrom('atendimentos', 'a');
            $sql .= " WHERE dt_cheg <= :data ";
            if ($unidade > 0) {
                $sql .= " AND a.id_uni = :unidade";
            }
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();
            
            // limpa a tabela de senhas a serem exibidas no painel
            $query = $conn->prepare("DELETE FROM painel_senha");
            $query->execute();

            $conn->commit();
        } catch (Exception $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw new Exception($e->getMessage());
        }
    }
    
    private static function delFrom($table, $alias) {
        $sql = "DELETE ";
        if (\novosga\Config::DB_TYPE == 'mysql' || novosga\Config::DB_TYPE == 'mssql') {
            $sql .= "$alias ";
        }
        return $sql . "FROM $table $alias ";
    }
    
    public static function isNumeracaoServico() {
        $numeracao = \novosga\model\Configuracao::get(\novosga\model\util\Senha::TIPO_NUMERACAO);
        if ($numeracao) {
            return $numeracao->getValor() == \novosga\model\util\Senha::NUMERACAO_SERVICO;
        }
        return false;
    }
    
    public static function buscaAtendimento(Unidade $unidade, $id) {
        $em = DB::getEntityManager();
        $query = $em->createQuery("SELECT e FROM novosga\model\Atendimento e JOIN e.servicoUnidade su WHERE e.id = :id AND su.unidade = :unidade");
        $query->setParameter('id', (int) $id);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getOneOrNullResult();
    }
    
    public static function buscaAtendimentos(Unidade $unidade, $senha) {
        $em = DB::getEntityManager();
        $field = \novosga\business\AtendimentoBusiness::isNumeracaoServico() ? 'numeroSenhaServico' : 'numeroSenha';
        $cond = '';
        $sigla = strtoupper(substr($senha, 0, 1));
        // verificando se a letra foi informada (o primeiro caracter diferente do valor convertido para int)
        $porSigla = ctype_alpha($sigla);
        if ($porSigla) {
            $cond = 'e.siglaSenha = :sigla AND';
            $numeroSenha = (int) substr($senha, 1);
        } else {
            $numeroSenha = (int) $senha;
        }
        $query = $em->createQuery("
            SELECT 
                e 
            FROM 
                novosga\model\Atendimento e 
                JOIN e.servicoUnidade su 
            WHERE 
                e.$field = :numero AND $cond
                su.unidade = :unidade 
            ORDER BY 
                e.id
        ");
        $query->setParameter('numero', $numeroSenha);
        if ($porSigla) {
            $query->setParameter('sigla', $sigla);
        }
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }
    
}
