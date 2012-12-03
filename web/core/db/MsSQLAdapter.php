<?php

use \core\SGA;

/**
 * MsSQL Adapter
 * @author rogeriolino
 */
class MsSQLAdapter extends DefaultDatabaseAdapter {

    const DRIVER_DBLIB = 'dblib';
    
    private $driver = self::DRIVER_DBLIB;
    
    public function connect($host, $port, $user, $pass, $dbname) {
        parent::connect($host, $port, $user, $pass, $dbname);
        /* 
         * no linux o driver para o mssql (dblib) nao converte entre utf8(web) + iso-8859-1(banco)
         * entao adiciona um interceptor para converter para utf8 quando imprimir, e
         * para iso-8859-1 quando for salvar
         */
        if ($this->driver == self::DRIVER_DBLIB && strtoupper(SGA::CHARSET) == 'UTF-8') {
            $this->conn->setInterceptor(Connection::INTERCEPTOR_TYPE_FETCH, array($this, 'fetchInterceptor'));
            $this->conn->setInterceptor(Connection::INTERCEPTOR_TYPE_BIND, array($this, 'bindInterceptor'));
        }
    }
    
    public function dsn($host, $port, $user, $pass, $dbname) {
        return "{$this->driver}:host={$host};dbname={$dbname}";
    }
    
    protected function createQueryProvider() {
        return new MsSQLQueryProvider();
    }
    
    public static function fetchInterceptor($rs) {
        if (is_array($rs)) {
            foreach ($rs as $k => $v) {
                $rs[$k] = self::fetchInterceptor($v);
            }
        } else if (is_string($rs)) {
            if (mb_detect_encoding($rs, 'UTF-8', true) === false) {
                $rs = utf8_encode($rs);
            }
        }
        return $rs;
    }
    
    public static function bindInterceptor($param) {
        if (mb_detect_encoding($param, 'UTF-8', true) == 'UTF-8') {
            $param = utf8_decode($param);
        }
        return $param;
    }
    
    /**
     * 10-10-2012 - DBLIB doesnt support transaction
     * https://bugs.php.net/bug.php?id=58600
     */
    private function supportTransaction() {
        $phpversion = defined('PHP_VERSION_ID') ? PHP_VERSION_ID : 0;
        if ($this->driver == self::DRIVER_DBLIB && $phpversion < 50400) {
            throw new Exception(_('O driver DBLIB usado para conectar ao SQL Server não suporta transações na versão do PHP instalado. Favor atualizar sua versão para 5.4 ou superior.'));
        }
        return true;
    }
    
    public function begin() {
        if ($this->supportTransaction()) {
            parent::begin();
        }
    }
    
    public function commit() {
        if ($this->supportTransaction()) {
            parent::commit();
        }
    }
    
    public function rollback() {
        if ($this->supportTransaction()) {
            parent::rollback();
        }
    }
    
    public function lastInsertId($table = '', $id = '') {
        $stmt = $this->conn->prepare("SELECT IDENT_CURRENT('$table')");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
}
