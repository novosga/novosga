<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;

/**
 * ApiControllerInterface
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
interface ApiControllerInterface
{
    
    /**
     * @param mixed $id
     * @return \stdClass
     */
    public function find($id);
    
    /**
     * @param Request $request
     * @return array
     */
    public function search(Request $request);
    
    /**
     * @param \stdClass $object
     */
    public function add($object);
    
    /**
     * @param \stdClass $object
     */
    public function update($object);
    
    /**
     * @param \stdClass $object
     */
    public function remove($object);
    
}
