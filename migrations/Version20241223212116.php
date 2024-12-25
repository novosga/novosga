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

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\AbortMigration;

final class Version20241223212116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Webhook support';
    }

    public function up(Schema $schema): void
    {
        if ($this->platform instanceof MySQLPlatform) {
            $platform = 'mysql';
        } elseif ($this->platform instanceof PostgreSQLPlatform) {
            $platform = 'postgres';
        } else {
            throw new AbortMigration(
                sprintf('Unsupported database platform: %s', get_class($this->platform))
            );
        }

        $sql = file_get_contents(sprintf('%s/sql/v20241223212116__webhooks.%s.sql', __DIR__, $platform));
        $this->addSql($sql);
    }
}
