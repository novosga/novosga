<?php

/**
 * Queries portadas para o MsSQL
 * 
 * @author rogeriolino
 */
class MsSQLQueryProvider extends DefaultQueryProvider {
    
    protected function dateToChar($field, $format) {
        $code = 0;
        switch ($format) {
        case self::DATE_FORMAT_DATE:
            $code = 103;
            break;
        case self::DATE_FORMAT_TIME:
            $code = 108;
            break;
        case self::DATE_FORMAT_DATETIME:
            return "CONVERT(varchar, $field, 103) + ' ' + CONVERT(varchar, $field, 108)";
        case self::DATE_FORMAT_YM:
            return "CAST(DATEPART(yyyy, $field) as VARCHAR) + '-' + CAST(DATEPART(m, $field) as VARCHAR)";
        }
        return "CONVERT(varchar, $field, $code)";
    }
    
    protected function concat($exp1, $exp2) {
        return "$exp1 + $exp2";
    }
    
    protected function dateAvg($exp) {
        return "CAST(AVG(CAST($exp as float)) as datetime)";
    }
    
    /**
     * TODO: NOWAIT
     */
    public function get_ultima_senha_lock() {
        return $this->get_ultima_senha();
    }
    
    public function get_ultima_senha() {
        return "
            SELECT TOP 1
                num_senha, sigla_serv, id_atend
            FROM 
                atendimentos a 
            LEFT JOIN 
                uni_serv u 
                ON a.id_serv = u.id_serv AND a.id_uni = u.id_uni
            WHERE 
                id_stat IN (:ids_stat) AND 
                a.id_uni = :id_uni
            ORDER BY 
                num_senha DESC
        ";
    }
    
    public function get_tempos_medios_por_periodo() {
        $dt_atend = $this->dateToChar("dt_cheg", self::DATE_FORMAT_YM);
        return "
            SELECT 
                count(id_atend) as count_atend,
                $dt_atend as dt_atend,
                " . $this->dateToChar($this->dateAvg("dt_cha - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_espera,
                " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_total
            FROM 
                view_historico_atendimentos vha
            WHERE 
                dt_cheg >= :dt_min AND 
                dt_cheg <= :dt_max AND 
                id_stat = :id_stat AND 
                vha.id_uni IN (:ids_uni)
            GROUP BY 
                $dt_atend
            ORDER BY 
                " . $this->dateAvg("dt_cheg") . "
        ";
    }

    public function get_senha_msg_global() {
        return "SELECT TOP 1 msg_global FROM senha_uni_msg";
    }

    public function get_unidade_by_codigo() {
        return $this->getAllUnidades() . " WHERE CAST(cod_uni as character varying) LIKE :cod_uni + '%'";
    }
  
    protected function invokeProcedure($name, array $params) {
        return "EXEC $name " . join(',', $params);
    }
    
    /**
     * Sobrescrevendo porque no MsSQL é necessário declarar as variáveis de retorno
     */
    public function getLotacao_valida() {
        return "
            DECLARE @p_id_grupo integer, @p_id_cargo integer; 
            EXEC sp_get_lotacao_valida :id_usu, :id_grupo, @p_id_grupo output, @p_id_cargo output
        ";
    }
    
}