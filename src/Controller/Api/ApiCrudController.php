<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ApiControllerBase
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiCrudController extends ApiControllerBase
{
    abstract public function getEntityName();

    public function find($id)
    {
        $object = $this->getRepository()->find($id);
        
        if (!$object) {
            throw new NotFoundHttpException;
        }
        
        $json = $this->serialize($object);
        
        return new \Symfony\Component\HttpFoundation\Response($json, 200, [ 'Content-type' => 'application/json' ]);
    }

    public function search(Request $request)
    {
        $q      = explode(' ', $request->get('q'));
        $sort   = (string) $request->get('sort');
        $order  = strtolower((string) $request->get('order'));
        $limit  = $request->get('limit') ?? 25;
        $offset = $request->get('offset') ?? 0;
        
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
        
        $result = $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
        
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
     * @return \Doctrine\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        $repository = $this
            ->getManager()
            ->getRepository($this->getEntityName());
        
        return $repository;
    }
    
    protected function serialize($object)
    {
        $ctx = \JMS\Serializer\SerializationContext::create()
                ->enableMaxDepthChecks();
        
        $serializer = $this->getSerializer();
        $data       = $serializer->serialize($object, 'json', $ctx);
        return $data;
    }


    /**
     *
     * @param string $json
     * @param array  $args
     * @return object
     */
    protected function deserialize($json)
    {
        $serializer = $this->getSerializer();
        $object     = $serializer->deserialize($json, $this->getEntityName(), 'json');
        
        return $object;
    }
}
