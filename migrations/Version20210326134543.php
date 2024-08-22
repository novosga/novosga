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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210326134543 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Improviments';
    }

    public function up(Schema $schema) : void
    {
        if (!$schema->getTable('clientes')->hasColumn('telefone')) {
            $this->addSql("ALTER TABLE clientes ADD telefone VARCHAR(25) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD dt_nascimento DATE DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD genero VARCHAR(1) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD observacao LONGTEXT DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_pais VARCHAR(2) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_cep VARCHAR(25) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_estado VARCHAR(3) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_cidade VARCHAR(30) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_logradouro VARCHAR(60) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_numero VARCHAR(10) DEFAULT NULL");
            $this->addSql("ALTER TABLE clientes ADD end_complemento VARCHAR(15) DEFAULT NULL");
        }
        if (!$schema->getTable('prioridades')->hasColumn('cor')) {
            $this->addSql("ALTER TABLE prioridades ADD cor VARCHAR(255) DEFAULT NULL");
            $this->addSql("UPDATE prioridades SET cor = '#0091da' WHERE cor IS NULL AND peso = 0");
            $this->addSql("UPDATE prioridades SET cor = '#de231b' WHERE cor IS NULL AND peso > 0");
        }
        if (!$schema->getTable('servicos')->hasColumn('descricao')) {
            $this->addSql("ALTER TABLE servicos CHANGE descricao descricao VARCHAR(250) NOT NULL");
        }

        $this->addSql("ALTER TABLE painel_senha CHANGE local local VARCHAR(20) NOT NULL");

        if (!$schema->getTable('agendamentos')->hasColumn('oid')) {
            $this->addSql("ALTER TABLE agendamentos ADD oid VARCHAR(255) DEFAULT NULL");
            $this->addSql("CREATE INDEX agendamento_oid_index ON agendamentos (oid)");
        }
        if (!$schema->getTable('agendamentos')->hasColumn('situacao')) {
            $this->addSql("ALTER TABLE agendamentos ADD situacao VARCHAR(20) NOT NULL");
            $this->addSql("UPDATE agendamentos SET situacao = 'agendado' WHERE situacao IS NULL OR situacao = ''");
        }
    }
}
