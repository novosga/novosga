<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Controller\Actions;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * FindTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait FindTrait
{
    
    /**
     * @Route("")
     * @Method("GET")
     */
    public function findAction(Request $request)
    {
        return $this->search($request);
    }
    
}
