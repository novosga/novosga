
ALTER TABLE historico_atendimentos ADD COLUMN atendimento_id bigint;
ALTER TABLE historico_atendimentos ADD FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos (id);

CREATE TABLE contador (
    unidade_id INT NOT NULL,
    total INT NOT NULL DEFAULT 0,
    PRIMARY KEY(unidade_id)
);

ALTER TABLE contador ADD FOREIGN KEY (unidade_id) REFERENCES unidades (id);


CREATE TABLE atend_meta (
    atendimento_id bigint NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (atendimento_id, name) 
);

ALTER TABLE atend_meta ADD FOREIGN KEY (atendimento_id) REFERENCES atendimentos (id);

CREATE TABLE usu_meta (
    usuario_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (usuario_id, name) 
);

ALTER TABLE usu_meta ADD FOREIGN KEY (usuario_id) REFERENCES usuarios (id);

CREATE TABLE historico_atend_meta (
    atendimento_id bigint NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (atendimento_id, name) 
);

ALTER TABLE historico_atend_meta ADD FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos (id);

CREATE VIEW view_historico_atend_meta
AS
    SELECT 
        atend_meta.atendimento_id, 
        atend_meta.name, 
        atend_meta.value
    FROM 
        atend_meta
    UNION ALL 
    SELECT 
        historico_atend_meta.atendimento_id, 
        historico_atend_meta.name, 
        historico_atend_meta.value
    FROM 
        historico_atend_meta;