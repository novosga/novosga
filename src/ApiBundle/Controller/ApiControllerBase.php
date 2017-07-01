<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    
    /**
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    protected function getTranslator()
    {
        $translator = $this->get('translator');
        
        return $translator;
    }
    
    /**
     *
     * @param string $msg
     * @return string
     */
    protected function translate($id, array $params = [])
    {
        $translated = $this->getTranslator()->trans($id, $params);
        
        return $translated;
    }
}
