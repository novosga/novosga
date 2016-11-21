<?php

namespace ApiBundle\Controller\Actions;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * PutTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait PutTrait
{
    
    /**
     * @Route("/{id}")
     * @Method("PUT")
     */
    public function putAction(Request $request, $id)
    {
        $object = $this->getRepository()->find($id);
        
        $json = $request->getContent();
        $this->deserialize($json, ['object_to_populate' => $object]);
        
        return $this->update($object);
    }
    
}
