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

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
final class Version20260424000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds cor_prioridade column to painel_senha table';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->getTable('painel_senha')->hasColumn('cor_prioridade')) {
            $this->addSql('ALTER TABLE painel_senha ADD cor_prioridade VARCHAR(20) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->getTable('painel_senha')->hasColumn('cor_prioridade')) {
            $this->addSql('ALTER TABLE painel_senha DROP COLUMN cor_prioridade');
        }
    }
}
