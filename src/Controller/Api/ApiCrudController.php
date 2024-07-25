<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @template T of object
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiCrudController extends ApiControllerBase
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    /** @return class-string<T> */
    abstract public function getEntityName(): string;

    public function find(int $id): Response
    {
        $entity = $this->getRepository()->find($id);

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $this->json($entity);
    }

    public function search(Request $request): Response
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

    /** @param T $object */
    public function add(object $object): Response
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

    /** @param T $object */
    public function remove(object $object): Response
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

    /** @param T $object */
    public function update(object $object): Response
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

    /**
     * @param array<string,mixed> $ctx
     * @return T
     */
    protected function deserialize(string $json, array $ctx = []): object
    {
        $object = $this->serializer->deserialize($json, $this->getEntityName(), 'json', $ctx);

        return $object;
    }

    /** @return ServiceEntityRepository<T> */
    protected function getRepository()
    {
        $repository = $this
            ->getManager()
            ->getRepository($this->getEntityName());

        return $repository;
    }
}
