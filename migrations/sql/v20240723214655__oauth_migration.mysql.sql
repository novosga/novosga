CREATE TABLE oauth2_authorization_code (
    identifier CHAR(80) NOT NULL,
    client VARCHAR(32) NOT NULL,
    expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    user_identifier VARCHAR(128) DEFAULT NULL,
    scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)',
    revoked TINYINT (1) NOT NULL,
    INDEX IDX_509FEF5FC7440455 (client),
    PRIMARY KEY (identifier)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;

CREATE TABLE oauth2_refresh_token (
    identifier CHAR(80) NOT NULL,
    access_token CHAR(80) DEFAULT NULL,
    expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    revoked TINYINT (1) NOT NULL,
    INDEX IDX_4DD90732B6A2DD68 (access_token),
    PRIMARY KEY (identifier)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;

CREATE TABLE oauth2_client (
    identifier VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL,
    secret VARCHAR(128) DEFAULT NULL,
    redirect_uris TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_redirect_uri)',
    grants TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_grant)',
    scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)',
    active TINYINT (1) NOT NULL,
    allow_plain_text_pkce TINYINT (1) DEFAULT 0 NOT NULL,
    PRIMARY KEY (identifier)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;

CREATE TABLE oauth2_access_token (
    identifier CHAR(80) NOT NULL,
    client VARCHAR(32) NOT NULL,
    expiry DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    user_identifier VARCHAR(128) DEFAULT NULL,
    scopes TEXT DEFAULT NULL COMMENT '(DC2Type:oauth2_scope)',
    revoked TINYINT (1) NOT NULL,
    INDEX IDX_454D9673C7440455 (client),
    PRIMARY KEY (identifier)
) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;

ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE;

ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL;

ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE;
