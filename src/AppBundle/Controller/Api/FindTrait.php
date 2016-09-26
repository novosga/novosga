<?php

namespace AppBundle\Controller\Api;

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
     */
    public function findAction(Request $request)
    {
        return $this->search($request);
    }
    
}
