<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * UsuariosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/usuarios")
 */
class UsuariosController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Usuario::class);
    }
    
}
