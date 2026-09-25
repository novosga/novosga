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

final class Version20260925120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the missing foreign key on atendimentos/historico_atendimentos.local_id';
    }

    public function isTransactional(): bool
    {
        // ALTER TABLE causes an implicit commit on MySQL.
        return false;
    }

    public function up(Schema $schema): void
    {
        // local_id existe nas duas tabelas desde a Version2, mas nunca
        // ganhou uma FK de verdade -- so' a coluna solta. Isso deixava
        // excluir um Local em uso silenciosamente sem bloquear nada (o
        // Listener que fazia essa checagem em codigo tinha bug e consultava
        // a entidade errada, ver EventListener/LocalListener removido).
        //
        // Limpa referencias orfas antes de criar a constraint (dados
        // antigos podem ter local_id apontando pra um Local ja excluido
        // manualmente antes dessa correcao existir).
        $this->addSql(
            'UPDATE atendimentos SET local_id = NULL
             WHERE local_id IS NOT NULL AND local_id NOT IN (SELECT id FROM locais)'
        );
        $this->addSql(
            'UPDATE historico_atendimentos SET local_id = NULL
             WHERE local_id IS NOT NULL AND local_id NOT IN (SELECT id FROM locais)'
        );

        if (!$schema->getTable('atendimentos')->hasForeignKey('FK_ATENDIMENTOS_LOCAL')) {
            $this->addSql(
                'ALTER TABLE atendimentos
                 ADD CONSTRAINT FK_ATENDIMENTOS_LOCAL FOREIGN KEY (local_id) REFERENCES locais (id)'
            );
        }

        if (!$schema->getTable('historico_atendimentos')->hasForeignKey('FK_HISTORICO_ATENDIMENTOS_LOCAL')) {
            $this->addSql(
                'ALTER TABLE historico_atendimentos
                 ADD CONSTRAINT FK_HISTORICO_ATENDIMENTOS_LOCAL FOREIGN KEY (local_id) REFERENCES locais (id)'
            );
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->getTable('atendimentos')->hasForeignKey('FK_ATENDIMENTOS_LOCAL')) {
            $this->addSql('ALTER TABLE atendimentos DROP FOREIGN KEY FK_ATENDIMENTOS_LOCAL');
        }

        if ($schema->getTable('historico_atendimentos')->hasForeignKey('FK_HISTORICO_ATENDIMENTOS_LOCAL')) {
            $this->addSql('ALTER TABLE historico_atendimentos DROP FOREIGN KEY FK_HISTORICO_ATENDIMENTOS_LOCAL');
        }
    }
}
