<?php

/**
 * MySQLAdapter Adapter
 * 
 * @author rogeriolino
 */
class MySQLAdapter extends DefaultDatabaseAdapter {
  
    public function dsn($host, $port, $user, $pass, $dbname) {
        return 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbname;
    }
    
    protected function createQueryProvider() {
        return new PgSQLQueryProvider();
    }
    
    public function lastInsertId($table = '', $id = '') {
        return $this->getConnection()->lastInsertId();
    }
        
}

