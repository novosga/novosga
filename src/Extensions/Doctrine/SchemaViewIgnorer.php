<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Doctrine;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Novosga\Entity\ViewAtendimento;
use Novosga\Entity\ViewAtendimentoCodificado;

/**
 * DoctrineListener
 * @see http://kamiladryjanek.com/ignore-entity-or-table-when-running-doctrine2-schema-update-command/
 */
class SchemaViewIgnorer
{
    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $schema = $args->getSchema();
        $em     = $args->getEntityManager();
 
        $ignoredEntities = [
            ViewAtendimento::class,
            ViewAtendimentoCodificado::class,
        ];
        
        $ignoredTables = [];
        
        foreach ($ignoredEntities as $entityName) {
            $ignoredTables[] = $em->getClassMetadata($entityName)->getTableName();
        }
        
        foreach ($schema->getTableNames() as $tableName) {
            $tableName = substr($tableName, strpos($tableName, '.') + 1);
            
            if (in_array($tableName, $ignoredTables)) {
                $schema->dropTable($tableName);
            }
        }
    }
}
