CREATE TABLE oauth2_authorization_code (
    identifier CHAR(80) NOT NULL,
    client VARCHAR(32) NOT NULL,
    expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    user_identifier VARCHAR(128) DEFAULT NULL,
    scopes TEXT DEFAULT NULL,
    revoked BOOLEAN NOT NULL,
    PRIMARY KEY (identifier)
);

CREATE INDEX IDX_509FEF5FC7440455 ON oauth2_authorization_code (client);

COMMENT ON COLUMN oauth2_authorization_code.expiry IS '(DC2Type:datetime_immutable)';

COMMENT ON COLUMN oauth2_authorization_code.scopes IS '(DC2Type:oauth2_scope)';

CREATE TABLE oauth2_refresh_token (
    identifier CHAR(80) NOT NULL,
    access_token CHAR(80) DEFAULT NULL,
    expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    revoked BOOLEAN NOT NULL,
    PRIMARY KEY (identifier)
);

CREATE INDEX IDX_4DD90732B6A2DD68 ON oauth2_refresh_token (access_token);

COMMENT ON COLUMN oauth2_refresh_token.expiry IS '(DC2Type:datetime_immutable)';

CREATE TABLE oauth2_client (
    identifier VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL,
    secret VARCHAR(128) DEFAULT NULL,
    redirect_uris TEXT DEFAULT NULL,
    grants TEXT DEFAULT NULL,
    scopes TEXT DEFAULT NULL,
    active BOOLEAN NOT NULL,
    allow_plain_text_pkce BOOLEAN DEFAULT false NOT NULL,
    PRIMARY KEY (identifier)
);

COMMENT ON COLUMN oauth2_client.redirect_uris IS '(DC2Type:oauth2_redirect_uri)';

COMMENT ON COLUMN oauth2_client.grants IS '(DC2Type:oauth2_grant)';

COMMENT ON COLUMN oauth2_client.scopes IS '(DC2Type:oauth2_scope)';

CREATE TABLE oauth2_access_token (
    identifier CHAR(80) NOT NULL,
    client VARCHAR(32) NOT NULL,
    expiry TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    user_identifier VARCHAR(128) DEFAULT NULL,
    scopes TEXT DEFAULT NULL,
    revoked BOOLEAN NOT NULL,
    PRIMARY KEY (identifier)
);

CREATE INDEX IDX_454D9673C7440455 ON oauth2_access_token (client);

COMMENT ON COLUMN oauth2_access_token.expiry IS '(DC2Type:datetime_immutable)';

COMMENT ON COLUMN oauth2_access_token.scopes IS '(DC2Type:oauth2_scope)';

ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
