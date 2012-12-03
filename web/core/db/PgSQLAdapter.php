<?php

/**
 * PostgreSQL Adapter
 *
 */
class PgSQLAdapter extends DefaultDatabaseAdapter {
  
    public function dsn($host, $port, $user, $pass, $dbname) {
        return 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $dbname;
    }
    
    protected function createQueryProvider() {
        return new PgSQLQueryProvider();
    }
    
    public function lastInsertId($table = '', $id = '') {
        return $this->getConnection()->lastInsertId($table . '_' . $id . '_seq');
    }
        
}

