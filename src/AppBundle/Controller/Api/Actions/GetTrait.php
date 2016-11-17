<?php

namespace AppBundle\Controller\Api\Actions;

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
