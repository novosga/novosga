<?php

/**
 * PDO Statement Wrapper
 *
 * @author ralfilho
 */
class Statement extends PDOStatement {
    
    private $conn;
    private $wrapped;
    
    public function __construct(Connection $conn, PDOStatement $wrapped) {
        $this->conn = $conn;
        $this->wrapped = $wrapped;
    }
    
    public function execute($input_parameters = null) {
        return $this->wrapped->execute($input_parameters);
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
        $rs = $this->wrapped->fetch($fetch_style, $cursor_orientation, $cursor_offset);
        $rs = $this->conn->callInterceptor(Connection::INTERCEPTOR_TYPE_FETCH, $rs);
        return $rs;
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) {
        $variable = $this->conn->callInterceptor(Connection::INTERCEPTOR_TYPE_BIND, $variable);
        return $this->wrapped->bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null) {
        return $this->wrapped->bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) {
        if ($data_type == PDO::PARAM_STR) {
            $value = $this->conn->callInterceptor(Connection::INTERCEPTOR_TYPE_BIND, $value);
        }
        return $this->wrapped->bindValue($parameter, $value, $data_type);
    }

    public function rowCount() {
        return $this->wrapped->rowCount();
    }
    
    public function fetchColumn($column_number = 0) {
        $rs = $this->wrapped->fetchColumn($column_number);
        $rs = $this->conn->callInterceptor(Connection::INTERCEPTOR_TYPE_FETCH, $rs);
        return $rs;
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = array()) {
        if ($fetch_style !== null) {
            if ($fetch_argument !== null) {
                if ($ctor_args !== null) {
                    $rs = $this->wrapped->fetchAll($fetch_style, $fetch_argument, $ctor_args);
                } else {
                    $rs = $this->wrapped->fetchAll($fetch_style, $fetch_argument);
                }
            } else {
                $rs = $this->wrapped->fetchAll($fetch_style);
            }
        } else {
            $rs = $this->wrapped->fetchAll();
        }
        $rs = $this->conn->callInterceptor(Connection::INTERCEPTOR_TYPE_FETCH, $rs);
        return $rs;
    }

    public function fetchObject($class_name = "stdClass", $ctor_args = null) {
        return $this->wrapped->fetchObject($class_name, $ctor_args);
    }

    public function errorCode() {
        return $this->wrapped->errorCode();
    }

    public function errorInfo() {
        return $this->wrapped->errorInfo();
    }

    public function setAttribute($attribute, $value) {
        return $this->wrapped->setAttribute($attribute, $value);
    }

    public function getAttribute($attribute) {
        return $this->wrapped->getAttribute($attribute);
    }

    public function columnCount() {
        return $this->wrapped->columnCount();
    }

    public function getColumnMeta($column) {
        return $this->wrapped->getColumnMeta($column);
    }

    public function setFetchMode($mode, $params = array()) {
        return $this->wrapped->setFetchMode($mode, $params);
    }

    public function nextRowset() {
        return $this->wrapped->nextRowset();
    }

    public function closeCursor() {
        return $this->wrapped->closeCursor();
    }

    public function debugDumpParams() {
        return $this->wrapped->debugDumpParams();
    }

}
