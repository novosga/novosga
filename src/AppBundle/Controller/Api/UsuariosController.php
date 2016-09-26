<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * UsuariosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/api/usuarios")
 */
class UsuariosController extends ApiControllerBase
{
    
    use GetTrait,
        FindTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Usuario::class);
    }
    
}
