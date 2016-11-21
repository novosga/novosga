<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * ApiControllerBase
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiControllerBase extends Controller
{
    
    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        $manager = $this->getDoctrine()
                            ->getManager();
        
        return $manager;
    }
    
    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        $serializer = $this->get('jms_serializer');
        
        return $serializer;
    }
}