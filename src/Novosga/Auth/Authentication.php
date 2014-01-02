<?php
namespace Novosga\Auth;

use \Doctrine\ORM\EntityManager;

/**
 * Authentication
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class Authentication {
    
    const KEY = 'auth';
    protected $em;
    
    public function __construct(EntityManager $em, array $config) {
        $this->em = $em;
        $this->init($config);
    }
    
    public abstract function init(array $config);
    
    public abstract function auth($username, $password);
    
    public abstract function test();
    
}
