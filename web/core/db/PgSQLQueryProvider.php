<?php

/**
 * Queries portadas para o PostgreSQL
 *
 */
class PgSQLQueryProvider extends DefaultQueryProvider {
    
    protected function dateToChar($field, $format) {
        $mask = '';
        switch ($format) {
        case self::DATE_FORMAT_DATE:
            $mask = 'DD/MM/YYYY';
            break;
        case self::DATE_FORMAT_TIME:
            $mask = 'HH24:MI:SS';
            break;
        case self::DATE_FORMAT_DATETIME:
            $mask = 'DD/MM/YYYY HH24:MI:SS';
            break;
        case self::DATE_FORMAT_YM:
            $mask = 'YYYY-MM';
            break;
        }
        return "TO_CHAR($field, '$mask')";
    }
    
    protected function concat($exp1, $exp2) {
        return "$exp1 || $exp2";
    }
    
    protected function dateAvg($exp) {
        return "AVG($exp)";
    }
    
    protected function invokeProcedure($name, array $params) {
        return "SELECT $name(" . join(',', $params) . ")";
    }
    
    /**
     * Usando lock
     * @return type
     */
    public function get_proximo_atendimento() {
        return parent::get_proximo_atendimento() . " FOR UPDATE";
    }
    
    public function getUltimaSenha() {
        return parent::getUltimaSenha() . " FOR UPDATE OF a NOWAIT";
    }


}