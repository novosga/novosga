<?php

namespace Novosga\Auth;

use Doctrine\ORM\EntityManager;

/**
 * AuthenticationProvider.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AuthenticationProvider
{
    const KEY = 'auth';

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em, array $config)
    {
        $this->em = $em;
        $this->init($config);
    }

    abstract public function init(array $config);

    abstract public function auth($username, $password);

    abstract public function test();
}
