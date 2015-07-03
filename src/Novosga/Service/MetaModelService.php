<?php

namespace Novosga\Service;

/**
 * MetaModelService.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class MetaModelService extends ModelService
{
    abstract protected function getMetaClass();
    abstract protected function getMetaFieldname();

    protected function modelMetadata($entity, $name, $value = null)
    {
        $className = $this->getMetaClass();
        $field = $this->getMetaFieldname();
        if ($value === null) {
            return $this->em
                    ->createQuery("SELECT e FROM {$className} e WHERE e.{$field} = :entity AND e.name = :name")
                    ->setParameter('entity', $entity)
                    ->setParameter('name', $name)
                    ->getOneOrNullResult();
        } else {
            $meta = $this->meta($entity, $name);
            if (!$meta) {
                $meta = new $className();
            }
            $meta->setName($name);
            $meta->setValue($value);
            $meta->setEntity($entity);
            $this->em->persist($meta);
            $this->em->flush();

            return $meta;
        }
    }
}
