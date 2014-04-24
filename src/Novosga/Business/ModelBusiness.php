<?php
namespace Novosga\Business;

use \Doctrine\ORM\EntityManager;

/**
 * ModelBusiness
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class ModelBusiness {
    
    /**
     * @var EntityManager
     */
    protected $em;
    
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
}
