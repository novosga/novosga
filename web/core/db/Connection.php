<?php

/**
 * PDO Wrapper
 *
 * @author ralfilho
 */
class Connection extends PDO {
    
    const INTERCEPTOR_TYPE_FETCH = 1;
    const INTERCEPTOR_TYPE_BIND = 2;
    
    private $interceptors = array();
    
    public function __construct($dsn, $username, $passwd, $options = array()) {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param int $type
     * @param array $interceptor (classe + metodo)
     */
    public function setInterceptor($type, array $interceptor) {
        $this->interceptors[$type] = $interceptor;
    }
    
    public function callInterceptor($type, $arg) {
        if (isset($this->interceptors[$type])) {
            $interceptor = $this->interceptors[$type];
            $method = new ReflectionMethod($interceptor[0], $interceptor[1]);
            return $method->invokeArgs($interceptor[0], array($arg));
        }
        return $arg;
    }
        
    public function prepare($statement, $driver_options = array()) {
        $stmt = parent::prepare($statement, $driver_options);
        return new Statement($this, $stmt);
    }
    
}
