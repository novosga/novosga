<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * ServicosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/api/servicos")
 */
class ServicosController extends ApiControllerBase
{
    
    use GetTrait,
        FindTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Servico::class);
    }
    
}
