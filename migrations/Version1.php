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

final class Version1 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Init';
    }

    public function up(Schema $schema) : void
    {
        if ($this->platform instanceof MySQLPlatform) {
            $this->upMysql();
        } elseif ($this->platform instanceof PostgreSQLPlatform) {
            $this->upPostgres();
        } else {
            throw new AbortMigration(
                sprintf('Unsupported database platform: %s', get_class($this->platform))
            );
        }
    }

    private function upMysql(): void
    {
        $this->addSql("CREATE TABLE `agendamentos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cliente_id` int(11) DEFAULT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            `servico_id` int(11) DEFAULT NULL,
            `data` date NOT NULL,
            `hora` time NOT NULL,
            `data_confirmacao` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_2D12EA4ADE734E51` (`cliente_id`),
            KEY `IDX_2D12EA4AEDF4B99B` (`unidade_id`),
            KEY `IDX_2D12EA4A82E14982` (`servico_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `atendimentos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cliente_id` int(11) DEFAULT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            `servico_id` int(11) DEFAULT NULL,
            `prioridade_id` int(11) DEFAULT NULL,
            `usuario_id` int(11) DEFAULT NULL,
            `usuario_tri_id` int(11) DEFAULT NULL,
            `atendimento_id` int(11) DEFAULT NULL,
            `num_local` smallint(6) DEFAULT NULL,
            `dt_age` datetime DEFAULT NULL,
            `dt_cheg` datetime NOT NULL,
            `dt_cha` datetime DEFAULT NULL,
            `dt_ini` datetime DEFAULT NULL,
            `dt_fim` datetime DEFAULT NULL,
            `tempo_espera` int(11) DEFAULT NULL,
            `tempo_permanencia` int(11) DEFAULT NULL,
            `tempo_atendimento` int(11) DEFAULT NULL,
            `tempo_deslocamento` int(11) DEFAULT NULL,
            `status` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
            `resolucao` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `observacao` longtext COLLATE utf8mb4_unicode_ci,
            `senha_sigla` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
            `senha_numero` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_29E906E7DE734E51` (`cliente_id`),
            KEY `IDX_29E906E7EDF4B99B` (`unidade_id`),
            KEY `IDX_29E906E782E14982` (`servico_id`),
            KEY `IDX_29E906E7226EFC79` (`prioridade_id`),
            KEY `IDX_29E906E7DB38439E` (`usuario_id`),
            KEY `IDX_29E906E7875F1A79` (`usuario_tri_id`),
            KEY `IDX_29E906E776323123` (`atendimento_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->addSql("CREATE TABLE `atendimentos_codificados` (
            `servico_id` int(11) NOT NULL,
            `atendimento_id` int(11) NOT NULL,
            `valor_peso` smallint(6) NOT NULL,
            PRIMARY KEY (`servico_id`, `atendimento_id`),
            KEY `IDX_DDF47B2D82E14982` (`servico_id`),
            KEY `IDX_DDF47B2D76323123` (`atendimento_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `atendimentos_metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `atendimento_id` int(11) NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`,
            `atendimento_id`),
            KEY `IDX_4F7723EB76323123` (`atendimento_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `clientes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
            `documento` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `clientes_metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `cliente_id` int(11) NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`, `cliente_id`),
            KEY `IDX_23B81DEEDE734E51` (`cliente_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `contador` (
            `unidade_id` int(11) NOT NULL,
            `servico_id` int(11) NOT NULL,
            `numero` int(11) DEFAULT NULL,
            PRIMARY KEY (`unidade_id`, `servico_id`),
            KEY `IDX_E83EF8FAEDF4B99B` (`unidade_id`),
            KEY `IDX_E83EF8FA82E14982` (`servico_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `departamentos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
            `descricao` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
            `ativo` tinyint(1) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `historico_atendimentos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cliente_id` int(11) DEFAULT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            `servico_id` int(11) DEFAULT NULL,
            `prioridade_id` int(11) DEFAULT NULL,
            `usuario_id` int(11) DEFAULT NULL,
            `usuario_tri_id` int(11) DEFAULT NULL,
            `atendimento_id` int(11) DEFAULT NULL,
            `num_local` smallint(6) DEFAULT NULL,
            `dt_age` datetime DEFAULT NULL,
            `dt_cheg` datetime NOT NULL,
            `dt_cha` datetime DEFAULT NULL,
            `dt_ini` datetime DEFAULT NULL,
            `dt_fim` datetime DEFAULT NULL,
            `tempo_espera` int(11) DEFAULT NULL,
            `tempo_permanencia` int(11) DEFAULT NULL,
            `tempo_atendimento` int(11) DEFAULT NULL,
            `tempo_deslocamento` int(11) DEFAULT NULL,
            `status` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
            `resolucao` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `observacao` longtext COLLATE utf8mb4_unicode_ci,
            `senha_sigla` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
            `senha_numero` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_CBBDF95FDE734E51` (`cliente_id`),
            KEY `IDX_CBBDF95FEDF4B99B` (`unidade_id`),
            KEY `IDX_CBBDF95F82E14982` (`servico_id`),
            KEY `IDX_CBBDF95F226EFC79` (`prioridade_id`),
            KEY `IDX_CBBDF95FDB38439E` (`usuario_id`),
            KEY `IDX_CBBDF95F875F1A79` (`usuario_tri_id`),
            KEY `IDX_CBBDF95F76323123` (`atendimento_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `historico_atendimentos_codificados` (
            `servico_id` int(11) NOT NULL,
            `atendimento_id` int(11) NOT NULL,
            `valor_peso` smallint(6) NOT NULL,
            PRIMARY KEY (`servico_id`, `atendimento_id`),
            KEY `IDX_111248C282E14982` (`servico_id`),
            KEY `IDX_111248C276323123` (`atendimento_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `historico_atendimentos_metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `atendimento_id` int(11) NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`, `atendimento_id`),
            KEY `IDX_169630A576323123` (`atendimento_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->addSql("CREATE TABLE `locais` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQ_C823878C54BD530C` (`nome`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `lotacoes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `usuario_id` int(11) DEFAULT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            `perfil_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `lotacao_usuario_unidade_idx` (`usuario_id`, `unidade_id`),
            KEY `IDX_10E72C2FDB38439E` (`usuario_id`),
            KEY `IDX_10E72C2FEDF4B99B` (`unidade_id`),
            KEY `IDX_10E72C2F57291544` (`perfil_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `oauth_access_tokens` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `expires_at` int(11) DEFAULT NULL,
            `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQ_CA42527C5F37A13B` (`token`),
            KEY `IDX_CA42527C19EB6921` (`client_id`),
            KEY `IDX_CA42527CA76ED395` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
          
        $this->addSql("CREATE TABLE `oauth_clients` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `random_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `redirect_uris` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
            `secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `allowed_grant_types` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
            `description` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `oauth_refresh_tokens` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `expires_at` int(11) DEFAULT NULL,
            `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQ_5AB6875F37A13B` (`token`),
            KEY `IDX_5AB68719EB6921` (`client_id`),
            KEY `IDX_5AB687A76ED395` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `paineis` (
            `host` int(11) NOT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            `senha` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`host`),
            KEY `IDX_CE58BF05EDF4B99B` (`unidade_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `paineis_servicos` (
            `host` int(11) NOT NULL,
            `servico_id` int(11) NOT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`host`, `servico_id`),
            KEY `IDX_D98415D3CF2713FD` (`host`),
            KEY `IDX_D98415D382E14982` (`servico_id`),
            KEY `IDX_D98415D3EDF4B99B` (`unidade_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `painel_senha` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `servico_id` int(11) DEFAULT NULL,
            `unidade_id` int(11) DEFAULT NULL,
            `num_senha` int(11) NOT NULL,
            `sig_senha` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
            `msg_senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `local` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `num_local` smallint(6) NOT NULL,
            `peso` smallint(6) NOT NULL,
            `prioridade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `nome_cliente` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `documento_cliente` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_390182E682E14982` (`servico_id`),
            KEY `IDX_390182E6EDF4B99B` (`unidade_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `perfis` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            `descricao` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
            `modulos` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `prioridades` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
            `descricao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `peso` smallint(6) NOT NULL,
            `ativo` tinyint(1) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            `deleted_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `servicos` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `macro_id` int(11) DEFAULT NULL,
            `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            `descricao` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
            `ativo` tinyint(1) NOT NULL,
            `peso` smallint(6) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            `deleted_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_89DD09E3F43A187E` (`macro_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `servicos_metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `servico_id` int(11) NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`, `servico_id`),
            KEY `IDX_8E8BF0E482E14982` (`servico_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `servicos_unidades` (
            `servico_id` int(11) NOT NULL,
            `unidade_id` int(11) NOT NULL,
            `local_id` int(11) DEFAULT NULL,
            `departamento_id` int(11) DEFAULT NULL,
            `sigla` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
            `ativo` tinyint(1) NOT NULL,
            `peso` smallint(6) NOT NULL,
            `prioridade` tinyint(1) NOT NULL,
            `numero_inicial` int(11) NOT NULL,
            `numero_final` int(11) DEFAULT NULL,
            `incremento` int(11) NOT NULL,
            `mensagem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`servico_id`, `unidade_id`),
            KEY `IDX_C50F703482E14982` (`servico_id`),
            KEY `IDX_C50F7034EDF4B99B` (`unidade_id`),
            KEY `IDX_C50F70345D5A2101` (`local_id`),
            KEY `IDX_C50F70345A91C08D` (`departamento_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `servicos_usuarios` (
            `servico_id` int(11) NOT NULL,
            `unidade_id` int(11) NOT NULL,
            `usuario_id` int(11) NOT NULL,
            `peso` smallint(6) NOT NULL,
            PRIMARY KEY (`servico_id`, `unidade_id`, `usuario_id`),
            KEY `IDX_CF69430282E14982` (`servico_id`),
            KEY `IDX_CF694302EDF4B99B` (`unidade_id`),
            KEY `IDX_CF694302DB38439E` (`usuario_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `unidades` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
            `descricao` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
            `ativo` tinyint(1) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            `deleted_at` datetime DEFAULT NULL,
            `impressao_cabecalho` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
            `impressao_rodape` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
            `impressao_exibir_data` tinyint(1) NOT NULL,
            `impressao_exibir_prioridade` tinyint(1) NOT NULL,
            `impressao_exibir_nome_unidade` tinyint(1) NOT NULL,
            `impressao_exibir_nome_servico` tinyint(1) NOT NULL,
            `impressao_exibir_mensagem_servico` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `unidades_metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `unidade_id` int(11) NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`, `unidade_id`),
            KEY `IDX_A21ACF47EDF4B99B` (`unidade_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `usuarios` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `login` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `nome` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `sobrenome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `senha` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
            `ativo` tinyint(1) NOT NULL,
            `ultimo_acesso` datetime DEFAULT NULL,
            `ip` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `session_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `algorithm` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
            `admin` tinyint(1) NOT NULL,
            `salt` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            `deleted_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQ_EF687F2AA08CB10` (`login`),
            UNIQUE KEY `UNIQ_EF687F2E7927C74` (`email`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->addSql("CREATE TABLE `usuarios_metadata` (
            `namespace` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
            `usuario_id` int(11) NOT NULL,
            `value` json NOT NULL COMMENT '(DC2Type:json)',
            PRIMARY KEY (`namespace`, `name`, `usuario_id`),
            KEY `IDX_BD8E7838DB38439E` (`usuario_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->addSql("ALTER TABLE `agendamentos` ADD CONSTRAINT `FK_2D12EA4A82E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE `agendamentos` ADD CONSTRAINT `FK_2D12EA4ADE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)");
        $this->addSql("ALTER TABLE `agendamentos` ADD CONSTRAINT `FK_2D12EA4AEDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E7226EFC79` FOREIGN KEY (`prioridade_id`) REFERENCES `prioridades` (`id`)");
        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E776323123` FOREIGN KEY (`atendimento_id`) REFERENCES `atendimentos` (`id`)");
        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E782E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E7875F1A79` FOREIGN KEY (`usuario_tri_id`) REFERENCES `usuarios` (`id`)");
        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E7DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)");
        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E7DE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)");
        $this->addSql("ALTER TABLE atendimentos ADD CONSTRAINT `FK_29E906E7EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE atendimentos_codificados ADD CONSTRAINT `FK_DDF47B2D76323123` FOREIGN KEY (`atendimento_id`) REFERENCES `atendimentos` (`id`)");
        $this->addSql("ALTER TABLE atendimentos_codificados ADD CONSTRAINT `FK_DDF47B2D82E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");

        $this->addSql("ALTER TABLE atendimentos_metadata ADD CONSTRAINT `FK_4F7723EB76323123` FOREIGN KEY (`atendimento_id`) REFERENCES `atendimentos` (`id`)");

        $this->addSql("ALTER TABLE clientes_metadata ADD CONSTRAINT `FK_23B81DEEDE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)");

        $this->addSql("ALTER TABLE contador ADD CONSTRAINT `FK_E83EF8FA82E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE contador ADD CONSTRAINT `FK_E83EF8FAEDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95F226EFC79` FOREIGN KEY (`prioridade_id`) REFERENCES `prioridades` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95F76323123` FOREIGN KEY (`atendimento_id`) REFERENCES `historico_atendimentos` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95F82E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95F875F1A79` FOREIGN KEY (`usuario_tri_id`) REFERENCES `usuarios` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95FDB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95FDE734E51` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos ADD CONSTRAINT `FK_CBBDF95FEDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE historico_atendimentos_codificados ADD CONSTRAINT `FK_111248C276323123` FOREIGN KEY (`atendimento_id`) REFERENCES `historico_atendimentos` (`id`)");
        $this->addSql("ALTER TABLE historico_atendimentos_codificados ADD CONSTRAINT `FK_111248C282E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");

        $this->addSql("ALTER TABLE historico_atendimentos_metadata ADD CONSTRAINT `FK_169630A576323123` FOREIGN KEY (`atendimento_id`) REFERENCES `historico_atendimentos` (`id`)");

        $this->addSql("ALTER TABLE lotacoes ADD CONSTRAINT `FK_10E72C2F57291544` FOREIGN KEY (`perfil_id`) REFERENCES `perfis` (`id`)");
        $this->addSql("ALTER TABLE lotacoes ADD CONSTRAINT `FK_10E72C2FDB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)");
        $this->addSql("ALTER TABLE lotacoes ADD CONSTRAINT `FK_10E72C2FEDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE oauth_access_tokens ADD CONSTRAINT `FK_CA42527C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`)");
        $this->addSql("ALTER TABLE oauth_access_tokens ADD CONSTRAINT `FK_CA42527CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)");

        $this->addSql("ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT `FK_5AB68719EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_clients` (`id`)");
        $this->addSql("ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT `FK_5AB687A76ED395` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)");

        $this->addSql("ALTER TABLE paineis ADD CONSTRAINT `FK_CE58BF05EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE paineis_servicos ADD CONSTRAINT `FK_D98415D382E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE paineis_servicos ADD CONSTRAINT `FK_D98415D3CF2713FD` FOREIGN KEY (`host`) REFERENCES `paineis` (`host`)");
        $this->addSql("ALTER TABLE paineis_servicos ADD CONSTRAINT `FK_D98415D3EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE painel_senha ADD CONSTRAINT `FK_390182E6EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");
        $this->addSql("ALTER TABLE painel_senha ADD CONSTRAINT `FK_390182E682E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");

        $this->addSql("ALTER TABLE servicos ADD CONSTRAINT `FK_89DD09E3F43A187E` FOREIGN KEY (`macro_id`) REFERENCES `servicos` (`id`)");

        $this->addSql("ALTER TABLE servicos_metadata ADD CONSTRAINT `FK_8E8BF0E482E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");

        $this->addSql("ALTER TABLE servicos_unidades ADD CONSTRAINT `FK_C50F70345A91C08D` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`)");
        $this->addSql("ALTER TABLE servicos_unidades ADD CONSTRAINT `FK_C50F70345D5A2101` FOREIGN KEY (`local_id`) REFERENCES `locais` (`id`)");
        $this->addSql("ALTER TABLE servicos_unidades ADD CONSTRAINT `FK_C50F703482E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE servicos_unidades ADD CONSTRAINT `FK_C50F7034EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE servicos_usuarios ADD CONSTRAINT `FK_CF69430282E14982` FOREIGN KEY (`servico_id`) REFERENCES `servicos` (`id`)");
        $this->addSql("ALTER TABLE servicos_usuarios ADD CONSTRAINT `FK_CF694302DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)");
        $this->addSql("ALTER TABLE servicos_usuarios ADD CONSTRAINT `FK_CF694302EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");
        
        $this->addSql("ALTER TABLE unidades_metadata ADD CONSTRAINT `FK_A21ACF47EDF4B99B` FOREIGN KEY (`unidade_id`) REFERENCES `unidades` (`id`)");

        $this->addSql("ALTER TABLE usuarios_metadata ADD CONSTRAINT `FK_BD8E7838DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)");

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
                `atendimentos`.`atendimento_id` AS `atendimento_id`
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
                `historico_atendimentos`.`atendimento_id` AS `atendimento_id`
            FROM
                `historico_atendimentos`"
            );

        $this->addSql("CREATE VIEW `view_atendimentos_codificados` AS
            SELECT
                `atendimentos_codificados`.`valor_peso` AS `valor_peso`,
                `atendimentos_codificados`.`servico_id` AS `servico_id`,
                `atendimentos_codificados`.`atendimento_id` AS `atendimento_id` 
            FROM
                `atendimentos_codificados`
            UNION ALL
            SELECT
                `historico_atendimentos_codificados`.`valor_peso` AS `valor_peso`,
                `historico_atendimentos_codificados`.`servico_id` AS `servico_id`,
                `historico_atendimentos_codificados`.`atendimento_id` AS `atendimento_id`
            FROM 
                `historico_atendimentos_codificados`"
        );
    }

    private function upPostgres(): void
    {
        throw new AbortMigration("TODO");
    }
}
