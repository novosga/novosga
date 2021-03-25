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
use Doctrine\Persistence\ManagerRegistry;
use Novosga\Entity\Metadata;
use Novosga\Repository\MetadataRepositoryInterface;

/**
 * MetadataRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class MetadataRepository extends ServiceEntityRepository implements MetadataRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Metadata::class);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $namespace, string $name)
    {
        return $this->findOneBy([
            'namespace' => $namespace,
            'name'      => $name
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function set(string $namespace, string $name, $value)
    {
        $em = $this->getEntityManager();
        $metada = $this->get($namespace, $name);
        
        if ($metada instanceof Metadata) {
            $metada->setValue($value);
        } else {
            $class  = $this->getEntityName();
            $metada = new $class;
            $metada->setNamespace($namespace);
            $metada->setName($name);
            $metada->setValue($value);
        }
        
        $em->persist($metada);
        $em->flush();
        
        return $metada;
    }
}
