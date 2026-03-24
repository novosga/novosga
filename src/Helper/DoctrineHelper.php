<?php

declare(strict_types=1);

namespace App\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * DoctrineHelper
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DoctrineHelper
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function getManager(): ObjectManager
    {
        return $this->em;
    }

    /**
     * Returns all table columns
     * @param class-string $entity
     * @return array<mixed>
     */
    public function getEntityColumns(string $entity): array
    {
        $classMetadata = $this->em->getClassMetadata($entity);
        $columns       = $classMetadata->getColumnNames();
        $assocs        = $classMetadata->getAssociationNames();

        foreach ($assocs as $assoc) {
            $mapping = $classMetadata->getAssociationMapping($assoc);
            if (isset($mapping['joinColumns'])) {
                foreach ($mapping['joinColumns'] as $join) {
                    $column = $join['name'];
                    if (!in_array($column, $columns)) {
                        $columns[] = $column;
                    }
                }
            }
        }

        foreach ($classMetadata->embeddedClasses as $field => $embeddedClass) {
            $embeddedClassName = $embeddedClass['class'];
            if (!class_exists($embeddedClassName)) {
                continue;
            }
            $metadata = $this->em->getClassMetadata($embeddedClassName);
            foreach ($metadata->getColumnNames() as $column) {
                $column = $embeddedClass['columnPrefix'] . $column;
                if (!in_array($column, $columns)) {
                    $columns[] = $column;
                }
            }
        }

        return $columns;
    }
}
