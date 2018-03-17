<?php

namespace App\Infrastructure;

use Exception;
use App\Infrastructure\Storage\MySQLStorage;
use App\Infrastructure\Storage\PostgreSQLStorage;
use Doctrine\Common\Persistence\ObjectManager;
use Novosga\Infrastructure\StorageInterface;

/**
 * StorageFactory
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class StorageFactory
{
    public function createStorage(ObjectManager $om): StorageInterface
    {
        if ($om instanceof \Doctrine\ORM\EntityManager) {
            $conn     = $om->getConnection();
            $platform = $conn->getSchemaManager()->getDatabasePlatform();
            
            if ($platform instanceof \Doctrine\DBAL\Platforms\MySqlPlatform) {
                return new MySQLStorage($om);
            }
            
            if ($platform instanceof \Doctrine\DBAL\Platforms\PostgreSqlPlatform) {
                return new PostgreSQLStorage($om);
            }
            
            if ($platform instanceof \Doctrine\DBAL\Platforms\SQLServerPlatform) {
                // TODO: implement SQLServerStorage
            }
        }
        
        throw new Exception('Novo SGA storage implemantation not found');
    }
}
