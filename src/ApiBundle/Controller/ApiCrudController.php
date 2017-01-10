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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ApiControllerBase
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiCrudController extends ApiControllerBase
{
    
    private $entityName;
    
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
    }

    public function find($id)
    {
        $object = $this->getRepository()->find($id);
        
        if (!$object) {
            throw new NotFoundHttpException;
        }
        
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
        try {
            $this->getManager()->persist($object);
            $this->getManager()->flush();
        } catch (\Exception $e) {
            $object = [
                'error' => $e->getMessage()
            ];
        }
        
        return $this->json($object);
    }

    public function remove($object)
    {
        try {
            $this->getManager()->remove($object);
            $this->getManager()->flush();
        } catch (\Exception $e) {
            $object = [
                'error' => $e->getMessage()
            ];
        }
        
        
        return $this->json($object);
    }

    public function update($object)
    {
        try {
            $this->getManager()->merge($object);
            $this->getManager()->flush();
        } catch (\Exception $e) {
            $object = [
                'error' => $e->getMessage()
            ];
        }
        
        return $this->json($object);
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
    
    /**
     * 
     * @param string $json
     * @param array  $args
     * @return object
     */
    protected function deserialize($json, array $args = [])
    {
        $serializer = $this->getSerializer();
        $object = $serializer->deserialize($json, $this->entityName, 'json', $args);
        
        return $object;
    }
}