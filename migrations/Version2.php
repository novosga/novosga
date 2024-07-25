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

final class Version2 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Local vs Atendimento relationship';
    }

    public function up(Schema $schema) : void
    {
        if (!$schema->getTable('atendimentos')->hasColumn('local_id')) {
            $this->addSql('ALTER TABLE atendimentos ADD local_id INT DEFAULT NULL');
        }

        if (!$schema->getTable('historico_atendimentos')->hasColumn('local_id')) {
            $this->addSql('ALTER TABLE historico_atendimentos ADD local_id INT DEFAULT NULL');
        }

        $this->addSql('DELETE FROM usuarios_metadata WHERE name = \'atendimento.num_local\'');
        $this->addSql('UPDATE usuarios_metadata SET name = \'atendimento.num_local\' WHERE name = \'atendimento.local\'');

        if ($this->platform instanceof MySQLPlatform) {
            $this->createViewsMysql();
        } elseif ($this->platform instanceof PostgreSQLPlatform) {
            $this->createViewsPostgres();
        } else {
            throw new AbortMigration(
                sprintf('Unsupported database platform: %s', get_class($this->platform))
            );
        }
    }

    private function createViewsMysql(): void
    {
        $this->addSql("DROP VIEW `view_atendimentos`");
        $this->addSql("CREATE VIEW `view_atendimentos` AS
            SELECT
                `atendimentos`.`id` AS `id`,
                `atendimentos`.`num_local` AS `num_local`,
                `atendimentos`.`dt_age` AS `dt_age`,
                `atendimentos`.`dt_cheg` AS `dt_cheg`,
                `atendimentos`.`dt_cha` AS `dt_cha`,
                `atendimentos`.`dt_ini` AS `dt_ini`,
                `atendimentos`.`dt_fim` AS `dt_fim`,
                `atendimentos`.`tempo_espera` AS `tempo_espera`,
                `atendimentos`.`tempo_permanencia` AS `tempo_permanencia`,
                `atendimentos`.`tempo_atendimento` AS `tempo_atendimento`,
                `atendimentos`.`tempo_deslocamento` AS `tempo_deslocamento`,
                `atendimentos`.`status` AS `status`,
                `atendimentos`.`resolucao` AS `resolucao`,
                `atendimentos`.`observacao` AS `observacao`,
                `atendimentos`.`senha_sigla` AS `senha_sigla`,
                `atendimentos`.`senha_numero` AS `senha_numero`,
                `atendimentos`.`cliente_id` AS `cliente_id`,
                `atendimentos`.`unidade_id` AS `unidade_id`,
                `atendimentos`.`servico_id` AS `servico_id`,
                `atendimentos`.`prioridade_id` AS `prioridade_id`,
                `atendimentos`.`usuario_id` AS `usuario_id`,
                `atendimentos`.`usuario_tri_id` AS `usuario_tri_id`,
                `atendimentos`.`atendimento_id` AS `atendimento_id`,
                `atendimentos`.`local_id` AS `local_id` 
            FROM
                `atendimentos`
            UNION ALL
            SELECT
                `historico_atendimentos`.`id` AS `id`,
                `historico_atendimentos`.`num_local` AS `num_local`,
                `historico_atendimentos`.`dt_age` AS `dt_age`,
                `historico_atendimentos`.`dt_cheg` AS `dt_cheg`,
                `historico_atendimentos`.`dt_cha` AS `dt_cha`,
                `historico_atendimentos`.`dt_ini` AS `dt_ini`,
                `historico_atendimentos`.`dt_fim` AS `dt_fim`,
                `historico_atendimentos`.`tempo_espera` AS `tempo_espera`,
                `historico_atendimentos`.`tempo_permanencia` AS `tempo_permanencia`,
                `historico_atendimentos`.`tempo_atendimento` AS `tempo_atendimento`,
                `historico_atendimentos`.`tempo_deslocamento` AS `tempo_deslocamento`,
                `historico_atendimentos`.`status` AS `status`,
                `historico_atendimentos`.`resolucao` AS `resolucao`,
                `historico_atendimentos`.`observacao` AS `observacao`,
                `historico_atendimentos`.`senha_sigla` AS `senha_sigla`,
                `historico_atendimentos`.`senha_numero` AS `senha_numero`,
                `historico_atendimentos`.`cliente_id` AS `cliente_id`,
                `historico_atendimentos`.`unidade_id` AS `unidade_id`,
                `historico_atendimentos`.`servico_id` AS `servico_id`,
                `historico_atendimentos`.`prioridade_id` AS `prioridade_id`,
                `historico_atendimentos`.`usuario_id` AS `usuario_id`,
                `historico_atendimentos`.`usuario_tri_id` AS `usuario_tri_id`,
                `historico_atendimentos`.`atendimento_id` AS `atendimento_id`,
                `historico_atendimentos`.`local_id` AS `local_id`
            FROM
                `historico_atendimentos`"
        );
    }

    private function createViewsPostgres(): void
    {
        throw new AbortMigration('TODO');
    }
}
