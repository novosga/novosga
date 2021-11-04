<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Novosga\Entity\EntityMetadata;
use Novosga\Repository\EntityMetadataRepositoryInterface;

/**
 * EntityMetadataRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class EntityMetadataRepository extends ServiceEntityRepository implements EntityMetadataRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByNamespace($entity, string $namespace)
    {
        return $this->findBy([
            'entity' => $entity,
            'namespace' => $namespace,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($entity, string $namespace, string $name)
    {
        return $this->findOneBy([
            'entity' => $entity,
            'namespace' => $namespace,
            'name' => $name
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function set($entity, string $namespace, string $name, $value)
    {
        $em = $this->getEntityManager();
        $metada = $this->get($entity, $namespace, $name);
        
        if ($metada instanceof EntityMetadata) {
            $metada->setValue($value);
        } else {
            $metada = $this->get($entity, $namespace, $name);
            if (!$metada) {
                $class = $this->getEntityName();
                $metada = new $class;
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

    /**
     * {@inheritdoc}
     */
    public function remove($entity, string $namespace, string $name)
    {
        $em = $this->getEntityManager();
        $metada = $this->get($entity, $namespace, $name);
        if ($metada) {
            $em->remove($metada);
            $em->flush();
        }
    }
}
