<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * DefaultController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
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
