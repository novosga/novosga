<?php

namespace AppBundle\Controller\Api\Actions;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * PostTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait PostTrait
{
    
    /**
     * @Route("")
     * @Method("POST")
     */
    public function postAction(Request $request)
    {
        $json = $request->getContent();
        $object = $this->deserialize($json);
        
        return $this->add($object);
    }
    
}
