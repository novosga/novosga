
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


DROP VIEW view_historico_atendimentos;

CREATE VIEW view_historico_atendimentos 
AS
    SELECT 
        atendimentos.id, 
        atendimentos.unidade_id, 
        atendimentos.usuario_id, 
        atendimentos.usuario_tri_id, 
        atendimentos.servico_id, 
        atendimentos.prioridade_id, 
        atendimentos.atendimento_id, 
        atendimentos.status, 
        atendimentos.sigla_senha, 
        atendimentos.num_senha, 
        atendimentos.num_senha_serv, 
        atendimentos.nm_cli, 
        atendimentos.num_local, 
        atendimentos.dt_cheg, 
        atendimentos.dt_cha, 
        atendimentos.dt_ini, 
        atendimentos.dt_fim, 
        atendimentos.ident_cli 
    FROM 
        atendimentos 
    UNION ALL 
    SELECT 
        historico_atendimentos.id, 
        historico_atendimentos.unidade_id, 
        historico_atendimentos.usuario_id, 
        historico_atendimentos.usuario_tri_id, 
        historico_atendimentos.servico_id, 
        historico_atendimentos.prioridade_id, 
        historico_atendimentos.atendimento_id,
        historico_atendimentos.status, 
        historico_atendimentos.sigla_senha, 
        historico_atendimentos.num_senha, 
        historico_atendimentos.num_senha_serv, 
        historico_atendimentos.nm_cli, 
        historico_atendimentos.num_local, 
        historico_atendimentos.dt_cheg, 
        historico_atendimentos.dt_cha, 
        historico_atendimentos.dt_ini, 
        historico_atendimentos.dt_fim, 
        historico_atendimentos.ident_cli 
    FROM 
        historico_atendimentos;