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

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * FindTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait FindTrait
{
    
    /**
     * @Route("", methods={"GET"})
     */
    public function doFind(Request $request)
    {
        return $this->search($request);
    }
}
