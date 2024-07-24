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

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Repository\EntityMetadataRepositoryInterface;

/**
 * @template T of EntityMetadataInterface
 * @template E
 * @extends ServiceEntityRepository<T>
 * @implements EntityMetadataRepositoryInterface<T,E>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class EntityMetadataRepository extends ServiceEntityRepository implements EntityMetadataRepositoryInterface
{
    /**
     * @param E $entity
     * @return T[]
     */
    public function findByNamespace($entity, string $namespace): array
    {
        return $this->findBy([
            'entity' => $entity,
            'namespace' => $namespace,
        ]);
    }

    /**
     * @param E $entity
     * @return ?T
     */
    public function get($entity, string $namespace, string $name)
    {
        return $this->findOneBy([
            'entity' => $entity,
            'namespace' => $namespace,
            'name' => $name,
        ]);
    }

    /**
     * @param E $entity
     * @return T
     */
    public function set($entity, string $namespace, string $name, mixed $value = null)
    {
        $em = $this->getEntityManager();
        $metada = $this->get($entity, $namespace, $name);

        if ($metada instanceof EntityMetadataInterface) {
            $metada->setValue($value);
        } else {
            $metada = $this->get($entity, $namespace, $name);
            if (!$metada) {
                $class = $this->getEntityName();
                $metada = new $class();
                $metada->setEntity($entity);
                $metada->setNamespace($namespace);
                $metada->setName($name);
            }
            $metada->setValue($value);
        }

        $em->persist($metada);
        $em->flush();

        return $metada;
    }

    /** @param E $entity */
    public function remove($entity, string $namespace, string $name): void
    {
        $em = $this->getEntityManager();
        $metada = $this->get($entity, $namespace, $name);
        if ($metada) {
            $em->remove($metada);
            $em->flush();
        }
    }
}
