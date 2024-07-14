<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Migrations\AbstractVersion;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210326134543 extends AbstractVersion
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        if (!$this->existsColumn('clientes_metadata', 'namespace')) {
            $this->addSql("CREATE TABLE clientes_metadata (namespace VARCHAR(30) NOT NULL, name VARCHAR(30) NOT NULL, cliente_id INT NOT NULL, value JSON NOT NULL COMMENT '(DC2Type:json_array)', INDEX IDX_23B81DEEDE734E51 (cliente_id), PRIMARY KEY(namespace, name, cliente_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
            $this->addSql("ALTER TABLE clientes_metadata ADD CONSTRAINT FK_23B81DEEDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)");
        }
        if (!$this->existsColumn('clientes', 'telefone')) {
            $this->addSql("ALTER TABLE clientes ADD telefone VARCHAR(25) DEFAULT NULL, ADD dt_nascimento DATE DEFAULT NULL, ADD genero VARCHAR(1) DEFAULT NULL, ADD observacao LONGTEXT DEFAULT NULL, ADD end_pais VARCHAR(2) DEFAULT NULL, ADD end_cep VARCHAR(25) DEFAULT NULL, ADD end_estado VARCHAR(3) DEFAULT NULL, ADD end_cidade VARCHAR(30) DEFAULT NULL, ADD end_logradouro VARCHAR(60) DEFAULT NULL, ADD end_numero VARCHAR(10) DEFAULT NULL, ADD end_complemento VARCHAR(15) DEFAULT NULL");
        }
        if (!$this->existsColumn('prioridades', 'cor')) {
            $this->addSql("ALTER TABLE prioridades ADD cor VARCHAR(255) DEFAULT NULL");
            $this->addSql("UPDATE prioridades SET cor = '#0091da' WHERE cor IS NULL AND peso = 0");
            $this->addSql("UPDATE prioridades SET cor = '#de231b' WHERE cor IS NULL AND peso > 0");
        }
        if (!$this->existsColumn('servicos', 'descricao')) {
            $this->addSql("ALTER TABLE servicos CHANGE descricao descricao VARCHAR(250) NOT NULL");
        }
        $this->addSql("ALTER TABLE painel_senha CHANGE local local VARCHAR(20) NOT NULL");
        if (!$this->existsColumn('agendamentos', 'oid')) {
            $this->addSql("ALTER TABLE agendamentos ADD oid VARCHAR(255) DEFAULT NULL");
            $this->addSql("CREATE INDEX agendamento_oid_index ON agendamentos (oid)");
        }
        if (!$this->existsColumn('agendamentos', 'situacao')) {
            $this->addSql("ALTER TABLE agendamentos ADD situacao VARCHAR(20) NOT NULL");
            $this->addSql("UPDATE agendamentos SET situacao = 'agendado' WHERE situacao IS NULL OR situacao = ''");
        }
    }
}
