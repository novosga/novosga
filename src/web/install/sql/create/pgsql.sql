-- @adapter=PostgreSQL
-- @author=rogeriolino
-- @date=2012-12-06

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

--
-- tables
--

CREATE TABLE atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE atend_status (
    id_stat serial NOT NULL,
    nm_stat character varying(30) NOT NULL,
    desc_stat character varying(150) NOT NULL
);

CREATE TABLE atendimentos (
    id_atend bigserial NOT NULL,
    id_uni integer,
    id_usu integer,
    id_usu_tri integer NOT NULL,
    id_serv integer NOT NULL,
    id_pri integer NOT NULL,
    id_stat integer NOT NULL,
    sigla_senha character(1) NOT NULL,
    num_senha integer NOT NULL,
    nm_cli character varying(100) DEFAULT NULL::character varying,
    num_guiche smallint NOT NULL,
    dt_cheg timestamp with time zone NOT NULL,
    dt_cha timestamp with time zone,
    dt_ini timestamp with time zone,
    dt_fim timestamp with time zone,
    ident_cli character varying(11) DEFAULT NULL::character varying
);

CREATE TABLE cargos_aninhados (
    id_cargo serial NOT NULL,
    nm_cargo character varying(30) NOT NULL,
    desc_cargo character varying(140),
    esquerda integer NOT NULL,
    direita integer NOT NULL
);

CREATE TABLE cargos_mod_perm (
    id_cargo integer NOT NULL,
    id_mod integer NOT NULL,
    permissao integer NOT NULL
);

CREATE TABLE grupos_aninhados (
    id_grupo serial NOT NULL,
    nm_grupo character varying(40) NOT NULL,
    desc_grupo character varying(150) NOT NULL,
    esquerda integer NOT NULL,
    direita integer NOT NULL
);

CREATE TABLE config (
    chave varchar(150) NOT NULL,
    valor TEXT NOT NULL,
    tipo integer NOT NULL
);

CREATE TABLE historico_atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE historico_atendimentos (
    id_atend bigint NOT NULL,
    id_uni integer,
    id_usu integer,
    id_usu_tri integer NOT NULL,
    id_serv integer NOT NULL,
    id_pri integer NOT NULL,
    id_stat integer NOT NULL,
    sigla_senha character(1) NOT NULL,
    num_senha integer NOT NULL,
    nm_cli character varying(100) DEFAULT NULL::character varying,
    num_guiche smallint NOT NULL,
    dt_cheg timestamp with time zone NOT NULL,
    dt_cha timestamp with time zone,
    dt_ini timestamp with time zone,
    dt_fim timestamp with time zone,
    ident_cli character varying(11) DEFAULT NULL::character varying
);

CREATE TABLE modulos (
    id_mod serial NOT NULL,
    chave_mod character varying(50) NOT NULL,
    nm_mod character varying(25) NOT NULL,
    desc_mod character varying(100) NOT NULL,
    autor_mod character varying(25) NOT NULL,
    tipo_mod smallint NOT NULL,
    stat_mod smallint NOT NULL
);

CREATE TABLE paineis (
    id_uni integer NOT NULL,
    host integer NOT NULL
);

CREATE TABLE paineis_servicos (
    host integer NOT NULL,
    id_uni integer NOT NULL,
    id_serv integer NOT NULL
);

CREATE TABLE painel_senha (
    contador serial NOT NULL,
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    num_senha integer NOT NULL,
    sig_senha character(1) NOT NULL,
    msg_senha character varying(15) NOT NULL,
    nm_local character varying(15) NOT NULL,
    num_guiche smallint NOT NULL
);

CREATE TABLE prioridades (
    id_pri serial NOT NULL,
    nm_pri character varying(30) NOT NULL,
    desc_pri character varying(100) NOT NULL,
    peso_pri smallint NOT NULL,
    stat_pri smallint NOT NULL
);

CREATE TABLE serv_local (
    id_loc serial NOT NULL,
    nm_loc character varying(20) NOT NULL
);

CREATE TABLE serv_peso (
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE servicos (
    id_serv serial NOT NULL,
    id_macro integer,
    desc_serv character varying(100) NOT NULL,
    nm_serv character varying(50),
    stat_serv smallint
);

CREATE TABLE uni_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_loc integer NOT NULL,
    nm_serv character varying(50) NOT NULL,
    sigla_serv character(1) NOT NULL,
    stat_serv smallint NOT NULL
);

CREATE TABLE unidades (
    id_uni serial NOT NULL,
    id_grupo integer NOT NULL,
    cod_uni character varying(10) NOT NULL,
    nm_uni character varying(50) DEFAULT NULL::character varying,
    stat_uni smallint DEFAULT 1,
    stat_imp smallint DEFAULT 0,
    msg_imp varchar(100)
);

CREATE TABLE usu_grup_cargo (
    id_usu integer NOT NULL,
    id_grupo integer NOT NULL,
    id_cargo integer NOT NULL
);

CREATE TABLE usu_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_usu integer NOT NULL
);

CREATE TABLE usuarios (
    id_usu serial NOT NULL,
    login_usu character varying(20) NOT NULL,
    email_usu character varying(255) NOT NULL,
    nm_usu character varying(20) NOT NULL,
    ult_nm_usu character varying(100) NOT NULL,
    senha_usu character varying(100) NOT NULL,
    senha_reset_token character varying(100),
    senha_reset_expir timestamp with time zone,
    ult_acesso timestamp with time zone,
    stat_usu smallint NOT NULL,
    session_id character varying(40) NOT NULL
);

--
-- keys
--

ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_pkey PRIMARY KEY (id_atend, id_serv);

ALTER TABLE ONLY atend_status ADD CONSTRAINT atend_status_pkey PRIMARY KEY (id_stat);

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_pkey PRIMARY KEY (id_atend);

ALTER TABLE ONLY cargos_aninhados ADD CONSTRAINT cargos_aninhados_pkey PRIMARY KEY (id_cargo);

ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_pkey PRIMARY KEY (id_cargo, id_mod);

ALTER TABLE ONLY grupos_aninhados ADD CONSTRAINT grupos_aninhados_pkey PRIMARY KEY (id_grupo);

ALTER TABLE ONLY config ADD CONSTRAINT config_pkey PRIMARY KEY (chave);

ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_pkey PRIMARY KEY (id_atend, id_serv);

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_pkey PRIMARY KEY (id_atend);

ALTER TABLE ONLY modulos ADD CONSTRAINT modulos_pkey PRIMARY KEY (id_mod);

ALTER TABLE ONLY paineis ADD CONSTRAINT paineis_pkey PRIMARY KEY (host);

ALTER TABLE ONLY paineis_servicos ADD CONSTRAINT paineis_servicos_pkey PRIMARY KEY (host, id_serv);

ALTER TABLE ONLY painel_senha ADD CONSTRAINT painel_senha_pkey PRIMARY KEY (contador);

ALTER TABLE ONLY prioridades ADD CONSTRAINT prioridades_pkey PRIMARY KEY (id_pri);

ALTER TABLE ONLY serv_local ADD CONSTRAINT serv_local_pkey PRIMARY KEY (id_loc);

ALTER TABLE ONLY serv_peso ADD CONSTRAINT serv_peso_pkey PRIMARY KEY (id_serv);

ALTER TABLE ONLY servicos ADD CONSTRAINT servicos_pkey PRIMARY KEY (id_serv);

ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_pkey PRIMARY KEY (id_uni, id_serv);

ALTER TABLE ONLY unidades ADD CONSTRAINT unidades_pkey PRIMARY KEY (id_uni);

ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_pkey PRIMARY KEY (id_usu, id_grupo);

ALTER TABLE ONLY usu_serv ADD CONSTRAINT usu_serv_pkey PRIMARY KEY (id_uni, id_serv, id_usu);

ALTER TABLE ONLY usuarios ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id_usu);

ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_5 FOREIGN KEY (id_usu_tri) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_5 FOREIGN KEY (id_usu_tri) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY paineis ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY painel_senha ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY painel_senha ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY serv_peso ADD CONSTRAINT peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY servicos ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY uni_serv ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY unidades ADD CONSTRAINT unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY usu_serv ADD CONSTRAINT usu_serv_ibfk_1 FOREIGN KEY (id_serv, id_uni) REFERENCES uni_serv(id_serv, id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY usu_serv ADD CONSTRAINT usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- indexes
--

CREATE UNIQUE INDEX cod_uni ON unidades USING btree (cod_uni);

CREATE INDEX direita ON grupos_aninhados USING btree (direita);

CREATE INDEX esqdir ON grupos_aninhados USING btree (esquerda, direita);

CREATE INDEX esquerda ON grupos_aninhados USING btree (esquerda);

CREATE INDEX fki_atend_codif_ibfk_2 ON atend_codif USING btree (id_serv);

CREATE INDEX fki_atendimentos_ibfk_1 ON atendimentos USING btree (id_pri);

CREATE INDEX fki_atendimentos_ibfk_2 ON atendimentos USING btree (id_uni, id_serv);

CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos USING btree (id_stat);

CREATE INDEX fki_atendimentos_ibfk_4 ON atendimentos USING btree (id_usu);

CREATE INDEX fki_id_grupo ON unidades USING btree (id_grupo);

CREATE INDEX fki_servicos_ibfk_1 ON servicos USING btree (id_macro);

CREATE INDEX fki_uni_serv_ibfk_2 ON uni_serv USING btree (id_serv);

CREATE INDEX fki_uni_serv_ibfk_3 ON uni_serv USING btree (id_loc);

CREATE INDEX fki_usu_serv_ibfk_1 ON usu_serv USING btree (id_serv, id_uni);

CREATE INDEX fki_usu_serv_ibfk_2 ON usu_serv USING btree (id_usu);

CREATE UNIQUE INDEX local_serv_nm ON serv_local USING btree (nm_loc);

CREATE UNIQUE INDEX login_usu ON usuarios USING btree (login_usu);

CREATE UNIQUE INDEX modulos_chave ON modulos USING btree (chave_mod);

--
-- views
--

CREATE VIEW view_historico_atend_codif 
AS
    SELECT 
        atend_codif.id_atend, 
        atend_codif.id_serv, 
        atend_codif.valor_peso 
    FROM 
        atend_codif 
    UNION ALL 
    SELECT 
        historico_atend_codif.id_atend, 
        historico_atend_codif.id_serv, 
        historico_atend_codif.valor_peso 
    FROM 
        historico_atend_codif;


CREATE VIEW view_historico_atendimentos 
AS
    SELECT 
        atendimentos.id_atend, 
        atendimentos.id_uni, 
        atendimentos.id_usu, 
        atendimentos.id_usu_tri, 
        atendimentos.id_serv, 
        atendimentos.id_pri, 
        atendimentos.id_stat, 
        atendimentos.sigla_senha, 
        atendimentos.num_senha, 
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
        historico_atendimentos.id_atend, 
        historico_atendimentos.id_uni, 
        historico_atendimentos.id_usu, 
        historico_atendimentos.id_usu_tri, 
        historico_atendimentos.id_serv, 
        historico_atendimentos.id_pri, 
        historico_atendimentos.id_stat, 
        historico_atendimentos.sigla_senha, 
        historico_atendimentos.num_senha, 
        historico_atendimentos.nm_cli, 
        historico_atendimentos.num_guiche, 
        historico_atendimentos.dt_cheg, 
        historico_atendimentos.dt_cha, 
        historico_atendimentos.dt_ini, 
        historico_atendimentos.dt_fim, 
        historico_atendimentos.ident_cli 
    FROM 
        historico_atendimentos;

