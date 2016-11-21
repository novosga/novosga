<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * GruposController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/grupos")
 */
class GruposController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait,
        Actions\PostTrait,
        Actions\PutTrait,
        Actions\DeleteTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Grupo::class);
    }
    
}
