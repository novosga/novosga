<?php
namespace novosga\api;

use \Doctrine\ORM\EntityManager;

/**
 * Api
 *
 * @author rogeriolino
 */
abstract class Api {
    
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
}
