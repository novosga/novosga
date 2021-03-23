<?php

namespace App\Helper;

use Doctrine\Persistence\ObjectManager;

/**
 * DoctrineHelper
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DoctrineHelper
{
    /**
     * @var ObjectManager
     */
    protected $om;
    
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }
    
    public function getManager(): ObjectManager
    {
        return $this->om;
    }
    
    /**
     * Returns all table columns
     * @param string $entity
     * @return array
     */
    public function getEntityColumns(string $entity): array
    {
        $classMetadata = $this->om->getClassMetadata($entity);
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
            $metadata = $this->om->getClassMetadata($embeddedClass['class']);
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
