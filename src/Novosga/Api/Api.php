<?php

namespace Novosga\Api;

use Doctrine\ORM\EntityManager;

/**
 * Api.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class Api
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
}
