<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api\Actions;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * GetTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait GetTrait
{
    
    /**
     * @Route("/{id}")
     * @Method("GET")
     */
    public function getAction($id)
    {
        return $this->find($id);
    }
}
