<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * UnidadesController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/unidades")
 */
class UnidadesController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait,
        Actions\PostTrait,
        Actions\PutTrait,
        Actions\DeleteTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Unidade::class);
    }
    
}
