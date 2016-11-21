<?php

namespace ApiBundle\Controller\Actions;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * DeleteTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait DeleteTrait
{
    
    /**
     * @Route("/{id}")
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $object = $this->getRepository()->find($id);
        
        return $this->remove($object);
    }
    
}
