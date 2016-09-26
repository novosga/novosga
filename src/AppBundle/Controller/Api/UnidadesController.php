<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * UnidadesController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/api/unidades")
 */
class UnidadesController extends ApiControllerBase
{
    
    use GetTrait,
        FindTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Unidade::class);
    }
    
}
