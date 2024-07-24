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

namespace App\Infrastructure;

use Exception;
use App\Infrastructure\Storage\MySQLStorage;
use App\Infrastructure\Storage\PostgreSQLStorage;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Infrastructure\StorageInterface;

/**
 * StorageFactory
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class StorageFactory
{
    public static function createStorage(EntityManagerInterface $em): StorageInterface
    {
        $conn = $em->getConnection();
        $platform = $conn->getDatabasePlatform();

        if ($platform instanceof MySQLPlatform) {
            return new MySQLStorage($em);
        }

        if ($platform instanceof PostgreSQLPlatform) {
            return new PostgreSQLStorage($em);
        }

        throw new Exception('Novo SGA storage implemantation not found');
    }
}
