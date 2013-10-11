-- @adapter=PostgreSQL
-- @author=rogeriolino
-- @date=2012-12-06

--
-- tables
--

CREATE TABLE atend_codif (
    atendimento_id bigint NOT NULL,
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE atendimentos (
    id bigserial NOT NULL,
    unidade_id integer,
    usuario_id integer,
    usuario_tri_id integer NOT NULL,
    servico_id integer NOT NULL,
    prioridade_id integer NOT NULL,
    status integer NOT NULL,
    sigla_senha character(1) NOT NULL,
    num_senha integer NOT NULL,
    num_senha_serv integer NOT NULL,
    nm_cli character varying(100) DEFAULT NULL::character varying,
    num_guiche smallint NOT NULL,
    dt_cheg TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dt_cha TIMESTAMP(0) WITHOUT TIME ZONE,
    dt_ini TIMESTAMP(0) WITHOUT TIME ZONE,
    dt_fim TIMESTAMP(0) WITHOUT TIME ZONE,
    ident_cli character varying(11) DEFAULT NULL::character varying
);

CREATE TABLE cargos (
    id serial NOT NULL,
    nome character varying(30) NOT NULL,
    descricao character varying(140),
    esquerda integer NOT NULL,
    direita integer NOT NULL,
    nivel integer NOT NULL
);

CREATE TABLE cargos_mod_perm (
    cargo_id integer NOT NULL,
    modulo_id integer NOT NULL,
    permissao integer NOT NULL
);

CREATE TABLE grupos (
    id serial NOT NULL,
    nome character varying(40) NOT NULL,
    descricao character varying(150) NOT NULL,
    esquerda integer NOT NULL,
    direita integer NOT NULL,
    nivel integer NOT NULL
);

CREATE TABLE config (
    chave varchar(150) NOT NULL,
    valor TEXT NOT NULL,
    tipo integer NOT NULL
);

CREATE TABLE historico_atend_codif (
    atendimento_id bigint NOT NULL,
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE historico_atendimentos (
    id bigint NOT NULL,
    unidade_id integer,
    usuario_id integer,
    usuario_tri_id integer NOT NULL,
    servico_id integer NOT NULL,
    prioridade_id integer NOT NULL,
    status integer NOT NULL,
    sigla_senha character(1) NOT NULL,
    num_senha integer NOT NULL,
    num_senha_serv integer NOT NULL,
    nm_cli character varying(100) DEFAULT NULL::character varying,
    num_guiche smallint NOT NULL,
    dt_cheg TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    dt_cha TIMESTAMP(0) WITHOUT TIME ZONE,
    dt_ini TIMESTAMP(0) WITHOUT TIME ZONE,
    dt_fim TIMESTAMP(0) WITHOUT TIME ZONE,
    ident_cli character varying(11) DEFAULT NULL::character varying
);

CREATE TABLE modulos (
    id serial NOT NULL,
    chave character varying(50) NOT NULL,
    nome character varying(25) NOT NULL,
    descricao character varying(100) NOT NULL,
    autor character varying(25) NOT NULL,
    tipo smallint NOT NULL,
    status smallint NOT NULL
);

CREATE TABLE paineis (
    unidade_id integer NOT NULL,
    host integer NOT NULL
);

CREATE TABLE paineis_servicos (
    host integer NOT NULL,
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL
);

CREATE TABLE painel_senha (
    id serial NOT NULL,
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    num_senha integer NOT NULL,
    sig_senha character(1) NOT NULL,
    msg_senha character varying(20) NOT NULL,
    local character varying(15) NOT NULL,
    num_guiche smallint NOT NULL,
    peso integer NOT NULL
);

CREATE TABLE prioridades (
    id serial NOT NULL,
    nome character varying(30) NOT NULL,
    descricao character varying(100) NOT NULL,
    peso smallint NOT NULL,
    status smallint NOT NULL
);

CREATE TABLE locais (
    id serial NOT NULL,
    nome character varying(20) NOT NULL
);

CREATE TABLE serv_peso (
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE servicos (
    id serial NOT NULL,
    id_macro integer,
    descricao character varying(100) NOT NULL,
    nome character varying(50),
    status smallint
);

CREATE TABLE uni_serv (
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    local_id integer NOT NULL,
    nome character varying(50) NOT NULL,
    sigla character(1) NOT NULL,
    status smallint NOT NULL
);

CREATE TABLE unidades (
    id serial NOT NULL,
    grupo_id integer NOT NULL,
    codigo character varying(10) NOT NULL,
    nome character varying(50) DEFAULT NULL::character varying,
    status smallint DEFAULT 1,
    stat_imp smallint DEFAULT 0,
    msg_imp varchar(100)
);

CREATE TABLE usu_grup_cargo (
    usuario_id integer NOT NULL,
    grupo_id integer NOT NULL,
    cargo_id integer NOT NULL
);

CREATE TABLE usu_serv (
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    usuario_id integer NOT NULL
);

CREATE TABLE usuarios (
    id serial NOT NULL,
    login character varying(20) NOT NULL,
    nome character varying(20) NOT NULL,
    sobrenome character varying(100) NOT NULL,
    senha character varying(40) NOT NULL,
    ult_acesso TIMESTAMP(0) WITHOUT TIME ZONE,
    status smallint NOT NULL,
    session_id character varying(40) NOT NULL
);

--
-- keys
--

ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_pkey PRIMARY KEY (atendimento_id, servico_id);
ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY cargos ADD CONSTRAINT cargos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_pkey PRIMARY KEY (cargo_id, modulo_id);
ALTER TABLE ONLY grupos ADD CONSTRAINT grupos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY config ADD CONSTRAINT config_pkey PRIMARY KEY (chave);
ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_pkey PRIMARY KEY (atendimento_id, servico_id);
ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY modulos ADD CONSTRAINT modulos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY paineis ADD CONSTRAINT paineis_pkey PRIMARY KEY (host);
ALTER TABLE ONLY paineis_servicos ADD CONSTRAINT paineis_servicos_pkey PRIMARY KEY (host, servico_id);
ALTER TABLE ONLY painel_senha ADD CONSTRAINT painel_senha_pkey PRIMARY KEY (id);
ALTER TABLE ONLY prioridades ADD CONSTRAINT prioridades_pkey PRIMARY KEY (id);
ALTER TABLE ONLY locais ADD CONSTRAINT locais_pkey PRIMARY KEY (id);
ALTER TABLE ONLY serv_peso ADD CONSTRAINT serv_peso_pkey PRIMARY KEY (servico_id);
ALTER TABLE ONLY servicos ADD CONSTRAINT servicos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_pkey PRIMARY KEY (unidade_id, servico_id);
ALTER TABLE ONLY unidades ADD CONSTRAINT unidades_pkey PRIMARY KEY (id);
ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_pkey PRIMARY KEY (usuario_id, grupo_id);
ALTER TABLE ONLY usu_serv ADD CONSTRAINT usu_serv_pkey PRIMARY KEY (unidade_id, servico_id, usuario_id);
ALTER TABLE ONLY usuarios ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY paineis ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv (unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY painel_senha ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY painel_senha ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY serv_peso ADD CONSTRAINT peso_ibfk_1 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY servicos ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (local_id) REFERENCES locais(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY unidades ADD CONSTRAINT unidades_grupo_id_fkey FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_2 FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_3 FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY usu_serv ADD CONSTRAINT usu_serv_ibfk_1 FOREIGN KEY (servico_id, unidade_id) REFERENCES uni_serv(servico_id, unidade_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE ONLY usu_serv ADD CONSTRAINT usu_serv_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- indexes
--

CREATE UNIQUE INDEX codigo ON unidades USING btree (codigo);
CREATE INDEX direita ON grupos USING btree (direita);
CREATE INDEX esqdir ON grupos USING btree (esquerda, direita);
CREATE INDEX esquerda ON grupos USING btree (esquerda);
CREATE INDEX fki_atend_codif_ibfk_2 ON atend_codif USING btree (servico_id);
CREATE INDEX fki_atendimentos_ibfk_1 ON atendimentos USING btree (prioridade_id);
CREATE INDEX fki_atendimentos_ibfk_2 ON atendimentos USING btree (unidade_id, servico_id);
CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos USING btree (status);
CREATE INDEX fki_atendimentos_ibfk_4 ON atendimentos USING btree (usuario_id);
CREATE INDEX fki_grupo_id ON unidades USING btree (grupo_id);
CREATE INDEX fki_servicos_ibfk_1 ON servicos USING btree (id_macro);
CREATE INDEX fki_uni_serv_ibfk_2 ON uni_serv USING btree (servico_id);
CREATE INDEX fki_uni_serv_ibfk_3 ON uni_serv USING btree (local_id);
CREATE INDEX fki_usu_serv_ibfk_1 ON usu_serv USING btree (servico_id, unidade_id);
CREATE INDEX fki_usu_serv_ibfk_2 ON usu_serv USING btree (usuario_id);
CREATE UNIQUE INDEX local_serv_nm ON locais USING btree (nome);
CREATE UNIQUE INDEX login ON usuarios USING btree (login);
CREATE UNIQUE INDEX modulos_chave ON modulos USING btree (chave);

--
-- views
--

CREATE VIEW view_historico_atend_codif 
AS
    SELECT 
        atend_codif.atendimento_id, 
        atend_codif.servico_id, 
        atend_codif.valor_peso 
    FROM 
        atend_codif 
    UNION ALL 
    SELECT 
        historico_atend_codif.atendimento_id, 
        historico_atend_codif.servico_id, 
        historico_atend_codif.valor_peso 
    FROM 
        historico_atend_codif;


CREATE VIEW view_historico_atendimentos 
AS
    SELECT 
        atendimentos.id, 
        atendimentos.unidade_id, 
        atendimentos.usuario_id, 
        atendimentos.usuario_tri_id, 
        atendimentos.servico_id, 
        atendimentos.prioridade_id, 
        atendimentos.status, 
        atendimentos.sigla_senha, 
        atendimentos.num_senha, 
        atendimentos.num_senha_serv, 
        atendimentos.nm_cli, 
        atendimentos.num_guiche, 
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
        historico_atendimentos.status, 
        historico_atendimentos.sigla_senha, 
        historico_atendimentos.num_senha, 
        historico_atendimentos.num_senha_serv, 
        historico_atendimentos.nm_cli, 
        historico_atendimentos.num_guiche, 
        historico_atendimentos.dt_cheg, 
        historico_atendimentos.dt_cha, 
        historico_atendimentos.dt_ini, 
        historico_atendimentos.dt_fim, 
        historico_atendimentos.ident_cli 
    FROM 
        historico_atendimentos;

