<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * ServicosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/servicos")
 */
class ServicosController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait,
        Actions\PostTrait,
        Actions\PutTrait,
        Actions\DeleteTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Servico::class);
    }
    
}
