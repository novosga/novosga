<?php
namespace core\auth;

/**
 * Authentication
 *
 * @author rogeriolino
 */
abstract class Authentication {
    
    const KEY = 'auth';

    public function __construct() {
        $config = \core\model\Configuracao::get(self::KEY);
        $this->init($config);
    }
    
    public abstract function init(array $config = null);
    
    public abstract function auth($username, $password);
    
}
