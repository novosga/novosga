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
    id_serv integer NOT NULL,
    id_pri integer NOT NULL,
    id_stat integer NOT NULL,
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

CREATE TABLE historico_atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);

CREATE TABLE historico_atendimentos (
    id_atend bigint NOT NULL,
    id_uni integer,
    id_usu integer,
    id_serv integer NOT NULL,
    id_pri integer NOT NULL,
    id_stat integer NOT NULL,
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
    img_mod character varying(150) DEFAULT NULL::character varying,
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
    contador integer NOT NULL,
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

CREATE TABLE usu_session (
    id_usu integer NOT NULL,
    session_id character varying(40) NOT NULL,
    stat_session integer NOT NULL
);

CREATE TABLE usuarios (
    id_usu serial NOT NULL,
    login_usu character varying(20) NOT NULL,
    nm_usu character varying(20) NOT NULL,
    ult_nm_usu character varying(100) NOT NULL,
    senha_usu character varying(40) NOT NULL,
    ult_acesso timestamp with time zone,
    stat_usu smallint NOT NULL
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

ALTER TABLE ONLY usu_session ADD CONSTRAINT usu_session_pkey PRIMARY KEY (id_usu);

ALTER TABLE ONLY usuarios ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id_usu);

ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atend_codif ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY atendimentos ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE ONLY historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

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

ALTER TABLE ONLY usu_session ADD CONSTRAINT usu_session_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

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
        atendimentos.id_serv, 
        atendimentos.id_pri, 
        atendimentos.id_stat, 
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
        historico_atendimentos.id_serv, 
        historico_atendimentos.id_pri, 
        historico_atendimentos.id_stat, 
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


--
-- procedures/functions
--

--
-- Move atendimentos da tabela "atendimentos" para a tabela "historico_atendimentos" e todas as
-- respectivas codificações da tabela "atend_codif" para a tabela "historico_atend_codif"
-- Somente atendimentos com "dt_cheg" anteriores ao parametro(p_dt_max) especificado serão movidos, use now() ou
-- uma data no futuro para mover todos os atendimentos existentes
--
CREATE FUNCTION sp_acumular_atendimentos(p_dt_max timestamp with time zone) RETURNS void
    AS $$
BEGIN
    -- salva atendimentos
    INSERT INTO historico_atendimentos
    SELECT a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.num_senha, a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli
    FROM atendimentos a
    WHERE dt_cheg <= p_dt_max
    FOR UPDATE;

    -- salva atendimentos codificados
    INSERT INTO historico_atend_codif
    SELECT ac.id_atend, ac.id_serv, ac.valor_peso
    FROM atend_codif ac
    WHERE id_atend IN (
        SELECT a.id_atend
        FROM atendimentos a
        WHERE dt_cheg <= p_dt_max
    )
    FOR UPDATE;

    -- limpa atendimentos codificados
    DELETE FROM atend_codif ac
    WHERE ac.id_atend IN (
        SELECT a.id_atend
        FROM atendimentos a
        WHERE dt_cheg <= p_dt_max
    );

    -- limpa atendimentos
    DELETE FROM atendimentos
    WHERE dt_cheg <= p_dt_max;
END;
$$
    LANGUAGE plpgsql;



--
-- Equivalente ao sp_acumular_atendimentos(), mas se limita a mover os atendimentos de uma determinada unidade
--
CREATE OR REPLACE FUNCTION sp_acumular_atendimentos_unidade(p_id_uni integer, p_dt_max timestamp with time zone)
  RETURNS void AS
$BODY$
BEGIN
    -- salva atendimentos da unidade
    INSERT INTO historico_atendimentos
    SELECT a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.num_senha, a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli
    FROM atendimentos a
    WHERE a.dt_cheg <= p_dt_max
    AND a.id_uni = p_id_uni
    FOR UPDATE;

    -- salva atendimentos codificados da unidade
    INSERT INTO historico_atend_codif
    SELECT ac.id_atend, ac.id_serv, ac.valor_peso
    FROM atend_codif ac
    WHERE id_atend IN (
        SELECT a.id_atend
        FROM atendimentos a
        WHERE dt_cheg <= p_dt_max
            AND a.id_uni = p_id_uni
    )
    FOR UPDATE;

    -- limpa atendimentos codificados da unidade
    DELETE FROM atend_codif ac
    WHERE ac.id_atend IN (
        SELECT id_atend
        FROM atendimentos a
        WHERE a.dt_cheg <= p_dt_max
        AND a.id_uni = p_id_uni
    );

    -- limpa atendimentos da unidade
    DELETE FROM atendimentos a
    WHERE dt_cheg <= p_dt_max
    AND a.id_uni = p_id_uni;
END;
$BODY$
  LANGUAGE plpgsql;


--
-- Retorna a lotação mais próxima do usuário que da acesso ao grupo especificado
--
-- Se o usuário estiver lotado no grupo "p_in_id_grupo", esta lotação é retornada
-- Caso contrário, o pai direto/indireto mais próximo onde o usuario estiver lotado será retornado.
-- Desta forma, um usuário que está lotado na raiz sempre possui uma lotação válida para qualquer
-- grupo.
--
CREATE FUNCTION sp_get_lotacao_valida(p_id_usu integer, p_in_id_grupo integer, OUT p_id_grupo integer, OUT p_id_cargo integer) RETURNS record
    AS $$
DECLARE
    v_uni_grupo_esq INTEGER;
    v_uni_grupo_dir INTEGER;
BEGIN
    SELECT esquerda, direita
    INTO v_uni_grupo_esq, v_uni_grupo_dir
    FROM grupos_aninhados
    WHERE id_grupo = p_in_id_grupo;

    SELECT ugc.id_cargo, ugc.id_grupo
    FROM usu_grup_cargo ugc
    INTO p_id_cargo, p_id_grupo
    INNER JOIN grupos_aninhados ga
        ON (ugc.id_grupo = ga.id_grupo)
    WHERE id_usu = p_id_usu
        AND esquerda <= v_uni_grupo_esq
        AND direita >= v_uni_grupo_dir
    ORDER BY esquerda DESC
    LIMIT 1;
END$$
    LANGUAGE plpgsql;


--
-- Insere uma session, caso não exista, ou atualiza caso exista.
-- Equivalente ao REPLACE do MySQL
--
CREATE FUNCTION sp_salvar_session_id(p_id_usu integer, p_session_id character varying) RETURNS void
    AS $$
BEGIN
    IF EXISTS( SELECT 1 FROM usu_session WHERE id_usu = p_id_usu ) THEN
        UPDATE usu_session
        SET session_id = p_session_id
        WHERE id_usu = p_id_usu;
    ELSE
        INSERT INTO usu_session VALUES( p_id_usu, p_session_id, 1 );
    END IF;
END;
$$
    LANGUAGE plpgsql;