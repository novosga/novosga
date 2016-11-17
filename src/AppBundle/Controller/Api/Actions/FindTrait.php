<?php

namespace AppBundle\Controller\Api\Actions;

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
