<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * AtendimentosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/api/atendimentos")
 */
class AtendimentosController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait;
    
    public function __construct()
    {
        parent::__construct(\Novosga\Entity\Atendimento::class);
    }
    
}
