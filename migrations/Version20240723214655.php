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

final class Version20240723214655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'OAuth Server migration';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('oauth2_client')) {
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

        if ($schema->hasTable('oauth_access_tokens')) {
            $this->addSql("DROP TABLE oauth_access_tokens");
        }
        if ($schema->hasTable('oauth_refresh_tokens')) {
            $this->addSql("DROP TABLE oauth_refresh_tokens");
        }
        if ($schema->hasTable('oauth_clients')) {
            $this->addSql("DROP TABLE oauth_clients");
        }
    }

    private function upMysql(): void
    {
        $this->addSql("CREATE TABLE oauth2_authorization_code (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)', revoked TINYINT(1) NOT NULL, INDEX IDX_509FEF5FC7440455 (client), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE oauth2_refresh_token (identifier CHAR(80) NOT NULL, access_token CHAR(80) DEFAULT NULL, expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', revoked TINYINT(1) NOT NULL, INDEX IDX_4DD90732B6A2DD68 (access_token), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE oauth2_client (identifier VARCHAR(32) NOT NULL, name VARCHAR(128) NOT NULL, secret VARCHAR(128) DEFAULT NULL, redirect_uris TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_redirect_uri)', grants TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_grant)', scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)', active TINYINT(1) NOT NULL, allow_plain_text_pkce TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE oauth2_access_token (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)', revoked TINYINT(1) NOT NULL, INDEX IDX_454D9673C7440455 (client), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB");
        $this->addSql("ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE");
    }

    private function upPostgres(): void
    {
        $this->addSql("CREATE TABLE oauth2_authorization_code (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))");
        $this->addSql("CREATE INDEX IDX_509FEF5FC7440455 ON oauth2_authorization_code (client)");
        $this->addSql("COMMENT ON COLUMN oauth2_authorization_code.expiry IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN oauth2_authorization_code.scopes IS '(DC2Type:oauth2_scope)'");
        $this->addSql("CREATE TABLE oauth2_refresh_token (identifier CHAR(80) NOT NULL, access_token CHAR(80) DEFAULT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))");
        $this->addSql("CREATE INDEX IDX_4DD90732B6A2DD68 ON oauth2_refresh_token (access_token)");
        $this->addSql("COMMENT ON COLUMN oauth2_refresh_token.expiry IS '(DC2Type:datetime_immutable)'");
        $this->addSql("CREATE TABLE oauth2_client (identifier VARCHAR(32) NOT NULL, name VARCHAR(128) NOT NULL, secret VARCHAR(128) DEFAULT NULL, redirect_uris TEXT DEFAULT NULL, grants TEXT DEFAULT NULL, scopes TEXT DEFAULT NULL, active BOOLEAN NOT NULL, allow_plain_text_pkce BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(identifier))");
        $this->addSql("COMMENT ON COLUMN oauth2_client.redirect_uris IS '(DC2Type:oauth2_redirect_uri)'");
        $this->addSql("COMMENT ON COLUMN oauth2_client.grants IS '(DC2Type:oauth2_grant)'");
        $this->addSql("COMMENT ON COLUMN oauth2_client.scopes IS '(DC2Type:oauth2_scope)'");
        $this->addSql("CREATE TABLE oauth2_access_token (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked BOOLEAN NOT NULL, PRIMARY KEY(identifier))");
        $this->addSql("CREATE INDEX IDX_454D9673C7440455 ON oauth2_access_token (client)");
        $this->addSql("COMMENT ON COLUMN oauth2_access_token.expiry IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN oauth2_access_token.scopes IS '(DC2Type:oauth2_scope)'");
        $this->addSql("ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
    }
}
