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
 * PutTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait PutTrait
{
    
    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function doPut(Request $request, $id)
    {
        $object = $this->getRepository()->find($id);
        
        $json = $request->getContent();
        $this->deserialize($json, ['object_to_populate' => $object]);
        
        return $this->update($object);
    }
}
