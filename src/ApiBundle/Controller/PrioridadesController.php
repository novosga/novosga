<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * PrioridadesController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/prioridades")
 */
class PrioridadesController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait,
        Actions\PostTrait,
        Actions\PutTrait,
        Actions\DeleteTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Prioridade::class);
    }
    
}
