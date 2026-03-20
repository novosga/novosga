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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260213170231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timezone field to unidades table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE unidades ADD timezone VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
      $this->addSql('ALTER TABLE unidades DROP COLUMN timezone');
    }
}
