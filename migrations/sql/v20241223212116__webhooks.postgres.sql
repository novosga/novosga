DO $body$
BEGIN

CREATE SEQUENCE webhooks_id_seq START 1;

CREATE TABLE webhooks (
    id INT NOT NULL,
    name VARCHAR(80) NOT NULL,
    url VARCHAR(255) NOT NULL,
    headers JSON NOT NULL,
    events JSON NOT NULL,
    enabled BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (id)
);

END $body$;
