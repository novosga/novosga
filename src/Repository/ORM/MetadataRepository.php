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

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Metadata;
use Novosga\Repository\MetadataRepositoryInterface;

/**
 * MetadataRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class MetadataRepository extends EntityRepository implements MetadataRepositoryInterface
{
    /**
     * @return Metadata
     */
    public function get($entity, $name)
    {
        return $this->findOneBy([
            'entity' => $entity,
            'name'   => $name
        ]);
    }
    
    /**
     * @return Metadata
     */
    public function set($entity, $name, $value)
    {
        $em = $this->getEntityManager();
        $metada = $this->get($entity, $name);
        
        if (!$metada) {
            $class  = $this->getEntityName();
            $metada = new $class;
            $metada->setEntity($entity);
            $metada->setValue($value);
            $em->persist($entity);
        } else {
            $metada->setValue($value);
            $em->merge($entity);
        }
        
        $em->flush();
        
        return $metada;
    }
}
