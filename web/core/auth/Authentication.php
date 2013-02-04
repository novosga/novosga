<?php
namespace core\auth;

/**
 * Authentication
 *
 * @author rogeriolino
 */
abstract class Authentication {
    
    const KEY = 'auth';
    
    public function __construct(array $config) {
        $this->init($config);
    }
    
    public abstract function init(array $config);
    
    public abstract function auth($username, $password);
    
}
