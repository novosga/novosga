<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
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

final class Version20260213170231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timezone field to unidades table';
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

        $sql = file_get_contents(sprintf('%s/sql/v20260213170231__add_timezone_to_unidades.%s.sql', __DIR__, $platform));
        $this->addSql($sql);
    }
}
