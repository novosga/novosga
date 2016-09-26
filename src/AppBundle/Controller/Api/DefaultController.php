<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * DefaultController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 * 
 * @Route("/api")
 */
class DefaultController extends Controller
{
    
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->json([
            'status' => 'ok'
        ]);
    }
    
}
