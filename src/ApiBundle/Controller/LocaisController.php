<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * LocaisController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/locais")
 */
class LocaisController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait,
        Actions\PostTrait,
        Actions\PutTrait,
        Actions\DeleteTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Local::class);
    }
    
}
