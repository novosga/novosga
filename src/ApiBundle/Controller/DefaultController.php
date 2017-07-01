<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            'status' => 'ok',
            'time'   => time(),
        ]);
    }
}
