
-- oauth2

CREATE TABLE oauth_clients (
    client_id VARCHAR(80) NOT NULL, 
    client_secret VARCHAR(80) NOT NULL, 
    redirect_uri VARCHAR(2000) NOT NULL, 
    grant_types VARCHAR(80), 
    user_id VARCHAR(80), 
    PRIMARY KEY (client_id)
);

CREATE TABLE oauth_scopes (
    type VARCHAR(255) NOT NULL DEFAULT "supported", 
    scope VARCHAR(2000), 
    client_id VARCHAR (80)
);

CREATE TABLE oauth_access_tokens (
    access_token VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000), 
    PRIMARY KEY (access_token)
);

CREATE TABLE oauth_refresh_tokens (
    refresh_token VARCHAR(40) NOT NULL, 
    client_id VARCHAR(80) NOT NULL, 
    user_id VARCHAR(255), 
    expires TIMESTAMP NOT NULL, 
    scope VARCHAR(2000), 
    PRIMARY KEY (refresh_token)
);
