<?php

namespace AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * ApiControllerBase
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiControllerBase extends Controller implements ApiControllerInterface
{
    
    private $entityName;
    
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
    }

    public function find($id)
    {
        $object = $this->getRepository()->find($id);
        
        return $this->json($object);
    }

    public function search(Request $request)
    {
        $q     = explode(' ', $request->get('q'));
        $sort  = (string) $request->get('sort');
        $order = strtolower((string) $request->get('order'));
        
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }
        
        $orderBy  = [];
        $criteria = [];
        
        if (strlen($sort)) {
            $orderBy[$sort] = $order;
        }
        
        foreach ($q as $i) {
            if (!empty($i)) {
                $param = explode(':', $i);
                if (count($param) === 2) {
                    $criteria[$param[0]] = $param[1];
                }
            }
        }
        
        $result = $this->getRepository()->findBy($criteria, $orderBy);
        
        return $this->json($result);
    }
    
    public function add($object)
    {
        $this->getManager()->persist($object);
        
        return $this->json($object);
    }

    public function remove($object)
    {
        $this->getManager()->remove($object);
        
        return $this->json($object);
    }

    public function update($object)
    {
        $this->getManager()->merge($object);
        
        return $this->json($object);
    }
    
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
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        $repository = $this->getManager()
                            ->getRepository($this->entityName);
        
        return $repository;
    }
}