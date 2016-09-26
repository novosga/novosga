<?php

namespace AppBundle\Controller\Api;

/**
 * GetTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait GetTrait
{
    
    /**
     * @Route("/{id}")
     */
    public function getAction($id)
    {
        return $this->find($id);
    }
    
}
