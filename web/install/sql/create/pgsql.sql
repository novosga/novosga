-- @adapter=PostgreSQL
-- @author=rogeriolino
-- @date=2012-12-06

--
-- PostgreSQL database dump
--

-- Started on 2009-02-27 15:05:25 BRT

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 400 (class 2612 OID 16386)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: -
--
SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;


--
-- TOC entry 1572 (class 1259 OID 27357)
-- Dependencies: 3
-- Name: atend_codif; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);


--
-- TOC entry 1574 (class 1259 OID 27362)
-- Dependencies: 3
-- Name: atend_status; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE atend_status (
    id_stat serial NOT NULL,
    nm_stat character varying(30) NOT NULL,
    desc_stat character varying(150) NOT NULL
);


--
-- TOC entry 1576 (class 1259 OID 27368)
-- Dependencies: 1890 1891 3
-- Name: atendimentos; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

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


--
-- TOC entry 1618 (class 1259 OID 28262)
-- Dependencies: 3
-- Name: cargos_aninhados; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE cargos_aninhados (
    id_cargo serial NOT NULL,
    nm_cargo character varying(30) NOT NULL,
    desc_cargo character varying(140),
    esquerda integer NOT NULL,
    direita integer NOT NULL
);


--
-- TOC entry 1579 (class 1259 OID 27380)
-- Dependencies: 3
-- Name: cargos_mod_perm; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE cargos_mod_perm (
    id_cargo integer NOT NULL,
    id_mod integer NOT NULL,
    permissao integer NOT NULL
);


--
-- TOC entry 1581 (class 1259 OID 27385)
-- Dependencies: 3
-- Name: grupos_aninhados; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE grupos_aninhados (
    id_grupo serial NOT NULL,
    nm_grupo character varying(40) NOT NULL,
    desc_grupo character varying(150) NOT NULL,
    esquerda integer NOT NULL,
    direita integer NOT NULL
);


--
-- TOC entry 1608 (class 1259 OID 28145)
-- Dependencies: 3
-- Name: historico_atend_codif; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE historico_atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);


--
-- TOC entry 1607 (class 1259 OID 28118)
-- Dependencies: 1908 1909 3
-- Name: historico_atendimentos; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

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

--
-- TOC entry 1585 (class 1259 OID 27397)
-- Dependencies: 1896 3
-- Name: modulos; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

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


--
-- TOC entry 1586 (class 1259 OID 27402)
-- Dependencies: 3
-- Name: paineis; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE paineis (
    id_uni integer NOT NULL,
    host integer NOT NULL
);

--
-- TOC entry 1586 (class 1259 OID 27402)
-- Dependencies: 3
-- Name: paineis; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE paineis_servicos (
    host integer NOT NULL,
    id_uni integer NOT NULL,
    id_serv integer NOT NULL
);

--
-- TOC entry 1588 (class 1259 OID 27407)
-- Dependencies: 3
-- Name: painel_senha; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

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


--
-- TOC entry 1590 (class 1259 OID 27413)
-- Dependencies: 3
-- Name: prioridades; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE prioridades (
    id_pri serial NOT NULL,
    nm_pri character varying(30) NOT NULL,
    desc_pri character varying(100) NOT NULL,
    peso_pri smallint NOT NULL,
    stat_pri smallint NOT NULL
);

--
-- TOC entry 1593 (class 1259 OID 27424)
-- Dependencies: 3
-- Name: serv_local; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE serv_local (
    id_loc serial NOT NULL,
    nm_loc character varying(20) NOT NULL
);


--
-- TOC entry 1594 (class 1259 OID 27428)
-- Dependencies: 3
-- Name: serv_peso; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE serv_peso (
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL
);


--
-- TOC entry 1596 (class 1259 OID 27433)
-- Dependencies: 3
-- Name: servicos; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE servicos (
    id_serv serial NOT NULL,
    id_macro integer,
    desc_serv character varying(100) NOT NULL,
    nm_serv character varying(50),
    stat_serv smallint
);

--
-- TOC entry 1599 (class 1259 OID 27446)
-- Dependencies: 3
-- Name: uni_serv; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE uni_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_loc integer NOT NULL,
    nm_serv character varying(50) NOT NULL,
    sigla_serv character(1) NOT NULL,
    stat_serv smallint NOT NULL
);


--
-- TOC entry 1601 (class 1259 OID 27451)
-- Dependencies: 1905 1906 3
-- Name: unidades; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE unidades (
    id_uni serial NOT NULL,
    id_grupo integer NOT NULL,
    cod_uni character varying(10) NOT NULL,
    nm_uni character varying(50) DEFAULT NULL::character varying,
    stat_uni smallint DEFAULT 1,
    stat_imp smallint DEFAULT 0,
    msg_imp varchar(100)
);


--
-- TOC entry 1602 (class 1259 OID 27456)
-- Dependencies: 3
-- Name: usu_grup_cargo; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE usu_grup_cargo (
    id_usu integer NOT NULL,
    id_grupo integer NOT NULL,
    id_cargo integer NOT NULL
);


--
-- TOC entry 1603 (class 1259 OID 27459)
-- Dependencies: 3
-- Name: usu_serv; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE usu_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_usu integer NOT NULL
);


--
-- TOC entry 1604 (class 1259 OID 27462)
-- Dependencies: 3
-- Name: usu_session; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE usu_session (
    id_usu integer NOT NULL,
    session_id character varying(40) NOT NULL,
    stat_session integer NOT NULL
);


--
-- TOC entry 1606 (class 1259 OID 27467)
-- Dependencies: 3
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

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
-- TOC entry 1610 (class 1259 OID 28162)
-- Dependencies: 1696 3
-- Name: view_historico_atend_codif; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW view_historico_atend_codif AS
    SELECT atend_codif.id_atend, atend_codif.id_serv, atend_codif.valor_peso FROM atend_codif UNION ALL SELECT historico_atend_codif.id_atend, historico_atend_codif.id_serv, historico_atend_codif.valor_peso FROM historico_atend_codif;


--
-- TOC entry 1609 (class 1259 OID 28158)
-- Dependencies: 1695 3
-- Name: view_historico_atendimentos; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW view_historico_atendimentos AS
    SELECT atendimentos.id_atend, atendimentos.id_uni, atendimentos.id_usu, atendimentos.id_serv, atendimentos.id_pri, atendimentos.id_stat, atendimentos.num_senha, atendimentos.nm_cli, atendimentos.num_guiche, atendimentos.dt_cheg, atendimentos.dt_cha, atendimentos.dt_ini, atendimentos.dt_fim, atendimentos.ident_cli FROM atendimentos UNION ALL SELECT historico_atendimentos.id_atend, historico_atendimentos.id_uni, historico_atendimentos.id_usu, historico_atendimentos.id_serv, historico_atendimentos.id_pri, historico_atendimentos.id_stat, historico_atendimentos.num_senha, historico_atendimentos.nm_cli, historico_atendimentos.num_guiche, historico_atendimentos.dt_cheg, historico_atendimentos.dt_cha, historico_atendimentos.dt_ini, historico_atendimentos.dt_fim, historico_atendimentos.ident_cli FROM historico_atendimentos;


--
-- TOC entry 24 (class 1255 OID 28166)
-- Dependencies: 3 400
-- Name: sp_acumular_atendimentos(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: -
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
-- TOC entry 20 (class 1255 OID 28268)
-- Dependencies: 400 3
-- Name: sp_atualizar_cargo(integer, integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: -
--

--
-- Atualiza os dados de um cargo
-- Se "p_id_pai" for diferente do atual, efetua uma modificação na arvore, tirando o nó do pai
-- atual, afiliando ao novo pai
--
--
CREATE FUNCTION sp_atualizar_cargo(p_id_cargo integer, p_id_pai integer, p_nm_cargo character varying, p_desc_cargo character varying) RETURNS void
    AS $$
DECLARE
    v_id_pai_atual INTEGER;
    v_esq_pai_atual INTEGER;
    v_dir_pai_atual INTEGER;
    v_esq_cargo INTEGER;
    v_dir_cargo INTEGER;
    v_len_cargo INTEGER;
    v_pai_direita INTEGER;
    v_esq_novo_pai INTEGER;
    v_dir_novo_pai INTEGER;
    v_len_novo_pai INTEGER;
    v_deslocamento INTEGER;
BEGIN
    UPDATE cargos_aninhados
    SET nm_cargo = p_nm_cargo, desc_cargo = p_desc_cargo
    WHERE id_cargo = p_id_cargo;

    SELECT pai.id_cargo, pai.esquerda, pai.direita
    INTO v_id_pai_atual, v_esq_pai_atual, v_dir_pai_atual
    FROM cargos_aninhados AS no,
    cargos_aninhados AS pai
    WHERE no.esquerda > pai.esquerda
        AND no.direita < pai.direita
    AND no.id_cargo = p_id_cargo
    ORDER BY pai.esquerda DESC
    LIMIT 1;

    IF v_id_pai_atual != p_id_pai THEN

        SELECT esquerda, direita, (direita - esquerda + 1)
        INTO v_esq_cargo, v_dir_cargo, v_len_cargo
        FROM cargos_aninhados
        WHERE id_cargo = p_id_cargo
        LIMIT 1;


        SELECT (direita - 1)
        INTO v_pai_direita
        FROM cargos_aninhados
        WHERE id_cargo = p_id_pai;


        UPDATE cargos_aninhados
        SET direita = direita + v_len_cargo
        WHERE direita > v_pai_direita;

        UPDATE cargos_aninhados
        SET esquerda = esquerda + v_len_cargo
        WHERE esquerda > v_pai_direita;


        SELECT esquerda, direita, (direita - esquerda + 1)
        INTO v_esq_novo_pai, v_dir_novo_pai, v_len_novo_pai
        FROM cargos_aninhados
        WHERE id_cargo = p_id_pai
        LIMIT 1;


        SELECT direita
        INTO v_dir_pai_atual
        FROM cargos_aninhados
        WHERE id_cargo = v_id_pai_atual
        LIMIT 1;

        SELECT esquerda, direita
        INTO v_esq_cargo, v_dir_cargo
        FROM cargos_aninhados
        WHERE id_cargo = p_id_cargo
        LIMIT 1;

        v_deslocamento := v_dir_novo_pai - v_dir_cargo - 1;

        UPDATE cargos_aninhados
        SET direita = direita + v_deslocamento,
            esquerda = esquerda + v_deslocamento
        WHERE esquerda >= v_esq_cargo
            AND direita <= v_dir_cargo;


        UPDATE cargos_aninhados
        SET direita = direita - v_len_cargo
        WHERE direita > v_dir_cargo;

        UPDATE cargos_aninhados
        SET esquerda = esquerda - v_len_cargo WHERE esquerda > v_dir_cargo;
    END IF;

END;
$$
    LANGUAGE plpgsql;


--
-- Atualiza os dados de um grupo
-- Se "p_id_pai" for diferente do atual, efetua uma modificação na arvore, tirando o nó do pai
-- atual, afiliando ao novo pai
--
--
CREATE FUNCTION sp_atualizar_grupo(p_id_grupo integer, p_id_pai integer, p_nm_grupo character varying, p_desc_grupo character varying) RETURNS void
    AS $$
DECLARE
    v_id_pai_atual INTEGER;
    v_esq_pai_atual INTEGER;
    v_dir_pai_atual INTEGER;
    v_esq_grupo INTEGER;
    v_dir_grupo INTEGER;
    v_len_grupo INTEGER;
    v_pai_direita INTEGER;
    v_esq_novo_pai INTEGER;
    v_dir_novo_pai INTEGER;
    v_len_novo_pai INTEGER;
    v_deslocamento INTEGER;
BEGIN
    UPDATE grupos_aninhados
    SET nm_grupo = p_nm_grupo, desc_grupo = p_desc_grupo
    WHERE id_grupo = p_id_grupo;

    SELECT pai.id_grupo, pai.esquerda, pai.direita
    INTO v_id_pai_atual, v_esq_pai_atual, v_dir_pai_atual
    FROM grupos_aninhados AS no,
    grupos_aninhados AS pai
    WHERE no.esquerda > pai.esquerda
        AND no.direita < pai.direita
    AND no.id_grupo = p_id_grupo
    ORDER BY pai.esquerda DESC
    LIMIT 1;

    IF v_id_pai_atual != p_id_pai THEN

        SELECT esquerda, direita, (direita - esquerda + 1)
        INTO v_esq_grupo, v_dir_grupo, v_len_grupo
        FROM grupos_aninhados
        WHERE id_grupo = p_id_grupo
        LIMIT 1;


        SELECT (direita - 1)
        INTO v_pai_direita
        FROM grupos_aninhados
        WHERE id_grupo = p_id_pai;


        UPDATE grupos_aninhados
        SET direita = direita + v_len_grupo
        WHERE direita > v_pai_direita;

        UPDATE grupos_aninhados
        SET esquerda = esquerda + v_len_grupo
        WHERE esquerda > v_pai_direita;


        SELECT esquerda, direita, (direita - esquerda + 1)
        INTO v_esq_novo_pai, v_dir_novo_pai, v_len_novo_pai
        FROM grupos_aninhados
        WHERE id_grupo = p_id_pai
        LIMIT 1;


        SELECT direita
        INTO v_dir_pai_atual
        FROM grupos_aninhados
        WHERE id_grupo = v_id_pai_atual
        LIMIT 1;

        SELECT esquerda, direita
        INTO v_esq_grupo, v_dir_grupo
        FROM grupos_aninhados
        WHERE id_grupo = p_id_grupo
        LIMIT 1;

        v_deslocamento := v_dir_novo_pai - v_dir_grupo - 1;

        UPDATE grupos_aninhados
        SET direita = direita + v_deslocamento,
            esquerda = esquerda + v_deslocamento
        WHERE esquerda >= v_esq_grupo
            AND direita <= v_dir_grupo;


        UPDATE grupos_aninhados
        SET direita = direita - v_len_grupo
        WHERE direita > v_dir_grupo;

        UPDATE grupos_aninhados
        SET esquerda = esquerda - v_len_grupo WHERE esquerda > v_dir_grupo;
    END IF;

END;
$$
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
-- TOC entry 21 (class 1255 OID 28269)
-- Dependencies: 3 400
-- Name: sp_inserir_cargo(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: -
--

-- Insere um nó na arvore de "cargos_aninhados"
CREATE FUNCTION sp_inserir_cargo(p_pai_id integer, p_nm_cargo character varying, p_desc_cargo character varying) RETURNS void
    AS $$
DECLARE
    v_pai_direita INTEGER;
BEGIN
    SELECT (direita - 1)
    INTO v_pai_direita
    FROM cargos_aninhados
    WHERE id_cargo = p_pai_id;

    UPDATE cargos_aninhados
    SET direita = direita + 2
    WHERE direita > v_pai_direita;

    UPDATE cargos_aninhados
    SET esquerda = esquerda + 2
    WHERE esquerda > v_pai_direita;

    INSERT INTO cargos_aninhados(nm_cargo, desc_cargo, esquerda, direita)
    VALUES(p_nm_cargo, p_desc_cargo, v_pai_direita + 1, v_pai_direita + 2);
END$$
    LANGUAGE plpgsql;


--
-- TOC entry 47 (class 1255 OID 27611)
-- Dependencies: 400 3
-- Name: sp_inserir_grupo(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: -
--
-- Insere um grupo na arvore de "grupos_aninhados"
CREATE FUNCTION sp_inserir_grupo(p_pai_id integer, p_nm_grupo character varying, p_desc_grupo character varying) RETURNS void
    AS $$
DECLARE
    v_pai_direita INTEGER;
BEGIN
    -- Obtem o valor "direita" do nó pai
    SELECT (direita - 1)
    INTO v_pai_direita
    FROM grupos_aninhados
    WHERE id_grupo = p_pai_id;

    -- Desloca todos elementos da arvore, para a direita (+2), abrindo um espaço de 2
    -- a ser usado apra inserir o nó
    UPDATE grupos_aninhados
    SET direita = direita + 2
    WHERE direita > v_pai_direita;
    -- continuação do deslocamento acima (agora para o "esquerda")
    UPDATE grupos_aninhados
    SET esquerda = esquerda + 2
    WHERE esquerda > v_pai_direita;

    -- Insere o nó no espaço que foi aberto
    INSERT INTO grupos_aninhados(nm_grupo, desc_grupo, esquerda, direita)
    VALUES(p_nm_grupo, p_desc_grupo, v_pai_direita + 1, v_pai_direita + 2);
END$$
    LANGUAGE plpgsql;


--
-- TOC entry 22 (class 1255 OID 28270)
-- Dependencies: 400 3
-- Name: sp_remover_cargo_cascata(integer); Type: FUNCTION; Schema: public; Owner: -
--
-- Remove um cargo, e seus filhos indiretos/diretos.
-- Suponha a Hierarquia: Presidente > Diretor > Gerente > Estágiario
-- Remover "Diretor" irá também remover Gerente e Estágiario
CREATE FUNCTION sp_remover_cargo_cascata(p_id_cargo integer) RETURNS void
    AS $$
DECLARE
    v_esquerda INTEGER;
    v_direita INTEGER;
    v_tamanho INTEGER;

BEGIN

    SELECT esquerda, direita, direita - esquerda + 1
    INTO v_esquerda, v_direita, v_tamanho
    FROM cargos_aninhados
    WHERE id_cargo = p_id_cargo;

    DELETE FROM cargos_aninhados
    WHERE esquerda BETWEEN v_esquerda AND v_direita;

    UPDATE cargos_aninhados
    SET direita = (direita - v_tamanho)
    WHERE direita > v_direita;

    UPDATE cargos_aninhados
    SET esquerda = (esquerda - v_tamanho)
    WHERE esquerda > v_direita;

END
$$
    LANGUAGE plpgsql;



--
-- Remove um grupo e seus filhos diretos/indiretos.
--
-- Exemplo: Brasil > Espírito Santo > Vitória
-- Remover "Espírito Santo" irá tambem remover "Vitória"
--
CREATE FUNCTION sp_remover_grupo_cascata(p_id_grupo integer) RETURNS void
    AS $$
DECLARE
    v_esquerda INTEGER;
    v_direita INTEGER;
    v_tamanho INTEGER;

BEGIN

SELECT esquerda, direita, direita - esquerda + 1
INTO v_esquerda, v_direita, v_tamanho
FROM grupos_aninhados
WHERE id_grupo = p_id_grupo;

DELETE FROM grupos_aninhados
WHERE esquerda BETWEEN v_esquerda AND v_direita;

UPDATE grupos_aninhados
SET direita = (direita - v_tamanho)
WHERE direita > v_direita;

UPDATE grupos_aninhados
SET esquerda = (esquerda - v_tamanho)
WHERE esquerda > v_direita;

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


--
-- TOC entry 1912 (class 2606 OID 28168)
-- Dependencies: 1572 1572 1572
-- Name: atend_codif_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY atend_codif
    ADD CONSTRAINT atend_codif_pkey PRIMARY KEY (id_atend, id_serv);


--
-- TOC entry 1915 (class 2606 OID 27474)
-- Dependencies: 1574 1574
-- Name: atend_status_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY atend_status
    ADD CONSTRAINT atend_status_pkey PRIMARY KEY (id_stat);


--
-- TOC entry 1917 (class 2606 OID 27476)
-- Dependencies: 1576 1576
-- Name: atendimentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY atendimentos
    ADD CONSTRAINT atendimentos_pkey PRIMARY KEY (id_atend);


--
-- TOC entry 1982 (class 2606 OID 28267)
-- Dependencies: 1618 1618
-- Name: cargos_aninhados_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY cargos_aninhados
    ADD CONSTRAINT cargos_aninhados_pkey PRIMARY KEY (id_cargo);


--
-- TOC entry 1925 (class 2606 OID 27478)
-- Dependencies: 1579 1579 1579
-- Name: cargos_mod_perm_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY cargos_mod_perm
    ADD CONSTRAINT cargos_mod_perm_pkey PRIMARY KEY (id_cargo, id_mod);



--
-- TOC entry 1930 (class 2606 OID 27482)
-- Dependencies: 1581 1581
-- Name: grupos_aninhados_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY grupos_aninhados
    ADD CONSTRAINT grupos_aninhados_pkey PRIMARY KEY (id_grupo);


--
-- TOC entry 1980 (class 2606 OID 28170)
-- Dependencies: 1608 1608 1608
-- Name: historico_atend_codif_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY historico_atend_codif
    ADD CONSTRAINT historico_atend_codif_pkey PRIMARY KEY (id_atend, id_serv);


--
-- TOC entry 1978 (class 2606 OID 28124)
-- Dependencies: 1607 1607
-- Name: historico_atendimentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_pkey PRIMARY KEY (id_atend);


--
-- TOC entry 1936 (class 2606 OID 27486)
-- Dependencies: 1585 1585
-- Name: modulos_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY modulos
    ADD CONSTRAINT modulos_pkey PRIMARY KEY (id_mod);


--
-- TOC entry 1938 (class 2606 OID 27488)
-- Dependencies: 1586 1586
-- Name: paineis_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY paineis
    ADD CONSTRAINT paineis_pkey PRIMARY KEY (host);

--
--

ALTER TABLE ONLY paineis_servicos
    ADD CONSTRAINT paineis_servicos_pkey PRIMARY KEY (host, id_serv);


--
-- TOC entry 1940 (class 2606 OID 27490)
-- Dependencies: 1588 1588
-- Name: painel_senha_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY painel_senha
    ADD CONSTRAINT painel_senha_pkey PRIMARY KEY (contador);


--
-- TOC entry 1942 (class 2606 OID 27492)
-- Dependencies: 1590 1590
-- Name: prioridades_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY prioridades
    ADD CONSTRAINT prioridades_pkey PRIMARY KEY (id_pri);


--
-- TOC entry 1947 (class 2606 OID 27496)
-- Dependencies: 1593 1593
-- Name: serv_local_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY serv_local
    ADD CONSTRAINT serv_local_pkey PRIMARY KEY (id_loc);


--
-- TOC entry 1949 (class 2606 OID 27498)
-- Dependencies: 1594 1594
-- Name: serv_peso_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY serv_peso
    ADD CONSTRAINT serv_peso_pkey PRIMARY KEY (id_serv);


--
-- TOC entry 1952 (class 2606 OID 27500)
-- Dependencies: 1596 1596
-- Name: servicos_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY servicos
    ADD CONSTRAINT servicos_pkey PRIMARY KEY (id_serv);



--
-- TOC entry 1960 (class 2606 OID 27506)
-- Dependencies: 1599 1599 1599
-- Name: uni_serv_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY uni_serv
    ADD CONSTRAINT uni_serv_pkey PRIMARY KEY (id_uni, id_serv);


--
-- TOC entry 1965 (class 2606 OID 27508)
-- Dependencies: 1601 1601
-- Name: unidades_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY unidades
    ADD CONSTRAINT unidades_pkey PRIMARY KEY (id_uni);


--
-- TOC entry 1967 (class 2606 OID 27510)
-- Dependencies: 1602 1602 1602
-- Name: usu_grup_cargo_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_pkey PRIMARY KEY (id_usu, id_grupo);


--
-- TOC entry 1971 (class 2606 OID 27512)
-- Dependencies: 1603 1603 1603 1603
-- Name: usu_serv_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY usu_serv
    ADD CONSTRAINT usu_serv_pkey PRIMARY KEY (id_uni, id_serv, id_usu);


--
-- TOC entry 1973 (class 2606 OID 27514)
-- Dependencies: 1604 1604
-- Name: usu_session_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY usu_session
    ADD CONSTRAINT usu_session_pkey PRIMARY KEY (id_usu);


--
-- TOC entry 1976 (class 2606 OID 27516)
-- Dependencies: 1606 1606
-- Name: usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace:
--

ALTER TABLE ONLY usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id_usu);



--
-- TOC entry 1961 (class 1259 OID 28244)
-- Dependencies: 1601
-- Name: cod_uni; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE UNIQUE INDEX cod_uni ON unidades USING btree (cod_uni);


--
-- TOC entry 1926 (class 1259 OID 27621)
-- Dependencies: 1581
-- Name: direita; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX direita ON grupos_aninhados USING btree (direita);


--
-- TOC entry 1927 (class 1259 OID 27622)
-- Dependencies: 1581 1581
-- Name: esqdir; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX esqdir ON grupos_aninhados USING btree (esquerda, direita);


--
-- TOC entry 1928 (class 1259 OID 27620)
-- Dependencies: 1581
-- Name: esquerda; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX esquerda ON grupos_aninhados USING btree (esquerda);


--
-- TOC entry 1913 (class 1259 OID 27627)
-- Dependencies: 1572
-- Name: fki_atend_codif_ibfk_2; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_atend_codif_ibfk_2 ON atend_codif USING btree (id_serv);


--
-- TOC entry 1918 (class 1259 OID 27623)
-- Dependencies: 1576
-- Name: fki_atendimentos_ibfk_1; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_atendimentos_ibfk_1 ON atendimentos USING btree (id_pri);


--
-- TOC entry 1919 (class 1259 OID 27624)
-- Dependencies: 1576 1576
-- Name: fki_atendimentos_ibfk_2; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_atendimentos_ibfk_2 ON atendimentos USING btree (id_uni, id_serv);


--
-- TOC entry 1920 (class 1259 OID 27625)
-- Dependencies: 1576
-- Name: fki_atendimentos_ibfk_3; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos USING btree (id_stat);


--
-- TOC entry 1921 (class 1259 OID 27626)
-- Dependencies: 1576
-- Name: fki_atendimentos_ibfk_4; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_atendimentos_ibfk_4 ON atendimentos USING btree (id_usu);


--
-- TOC entry 1962 (class 1259 OID 28394)
-- Dependencies: 1601
-- Name: fki_id_grupo; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_id_grupo ON unidades USING btree (id_grupo);

--
-- TOC entry 1950 (class 1259 OID 27631)
-- Dependencies: 1596
-- Name: fki_servicos_ibfk_1; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_servicos_ibfk_1 ON servicos USING btree (id_macro);


--
-- TOC entry 1957 (class 1259 OID 27639)
-- Dependencies: 1599
-- Name: fki_uni_serv_ibfk_2; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_uni_serv_ibfk_2 ON uni_serv USING btree (id_serv);


--
-- TOC entry 1958 (class 1259 OID 27640)
-- Dependencies: 1599
-- Name: fki_uni_serv_ibfk_3; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_uni_serv_ibfk_3 ON uni_serv USING btree (id_loc);


--
-- TOC entry 1968 (class 1259 OID 27641)
-- Dependencies: 1603 1603
-- Name: fki_usu_serv_ibfk_1; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_usu_serv_ibfk_1 ON usu_serv USING btree (id_serv, id_uni);


--
-- TOC entry 1969 (class 1259 OID 27642)
-- Dependencies: 1603
-- Name: fki_usu_serv_ibfk_2; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE INDEX fki_usu_serv_ibfk_2 ON usu_serv USING btree (id_usu);


--
-- TOC entry 1945 (class 1259 OID 27644)
-- Dependencies: 1593
-- Name: local_serv_nm; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE UNIQUE INDEX local_serv_nm ON serv_local USING btree (nm_loc);


--
-- TOC entry 1974 (class 1259 OID 28378)
-- Dependencies: 1606
-- Name: login_usu; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE UNIQUE INDEX login_usu ON usuarios USING btree (login_usu);


--
-- TOC entry 1934 (class 1259 OID 27643)
-- Dependencies: 1585
-- Name: modulos_chave; Type: INDEX; Schema: public; Owner: -; Tablespace:
--

CREATE UNIQUE INDEX modulos_chave ON modulos USING btree (chave_mod);


--
-- TOC entry 1986 (class 2606 OID 27776)
-- Dependencies: 1576 1916 1572
-- Name: atend_codif_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY atend_codif
    ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1985 (class 2606 OID 27649)
-- Dependencies: 1596 1572 1951
-- Name: atend_codif_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY atend_codif
    ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1987 (class 2606 OID 27654)
-- Dependencies: 1576 1590 1941
-- Name: atendimentos_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY atendimentos
    ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1988 (class 2606 OID 27659)
-- Dependencies: 1959 1576 1576 1599 1599
-- Name: atendimentos_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY atendimentos
    ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1989 (class 2606 OID 27664)
-- Dependencies: 1914 1576 1574
-- Name: atendimentos_ibfk_3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY atendimentos
    ADD CONSTRAINT atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1990 (class 2606 OID 27669)
-- Dependencies: 1975 1606 1576
-- Name: atendimentos_ibfk_4; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY atendimentos
    ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1991 (class 2606 OID 28281)
-- Dependencies: 1618 1981 1579
-- Name: cargos_mod_perm_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY cargos_mod_perm
    ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1992 (class 2606 OID 28001)
-- Dependencies: 1935 1579 1585
-- Name: cargos_mod_perm_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY cargos_mod_perm
    ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2016 (class 2606 OID 28148)
-- Dependencies: 1607 1977 1608
-- Name: historico_atend_codif_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY historico_atend_codif
    ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2017 (class 2606 OID 28153)
-- Dependencies: 1951 1596 1608
-- Name: historico_atend_codif_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY historico_atend_codif
    ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2013 (class 2606 OID 28125)
-- Dependencies: 1590 1607 1941
-- Name: historico_atendimentos_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2014 (class 2606 OID 28130)
-- Dependencies: 1607 1599 1959 1599 1607
-- Name: historico_atendimentos_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2015 (class 2606 OID 28135)
-- Dependencies: 1574 1914 1607
-- Name: historico_atendimentos_ibfk_3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2012 (class 2606 OID 28140)
-- Dependencies: 1606 1607 1975
-- Name: historico_atendimentos_ibfk_4; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- TOC entry 1995 (class 2606 OID 28016)
-- Dependencies: 1586 1964 1601
-- Name: paineis_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY paineis
    ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
--

ALTER TABLE ONLY paineis_servicos
    ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
--

ALTER TABLE ONLY paineis_servicos
    ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- TOC entry 1996 (class 2606 OID 28006)
-- Dependencies: 1964 1601 1588
-- Name: painel_senha_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY painel_senha
    ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1997 (class 2606 OID 28011)
-- Dependencies: 1588 1596 1951
-- Name: painel_senha_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY painel_senha
    ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 1999 (class 2606 OID 27679)
-- Dependencies: 1594 1596 1951
-- Name: peso_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY serv_peso
    ADD CONSTRAINT peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2000 (class 2606 OID 27684)
-- Dependencies: 1951 1596 1596
-- Name: servicos_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY servicos
    ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2001 (class 2606 OID 27689)
-- Dependencies: 1964 1599 1601
-- Name: uni_serv_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY uni_serv
    ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2002 (class 2606 OID 27694)
-- Dependencies: 1596 1951 1599
-- Name: uni_serv_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY uni_serv
    ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2003 (class 2606 OID 27699)
-- Dependencies: 1599 1946 1593
-- Name: uni_serv_ibfk_3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY uni_serv
    ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- TOC entry 2005 (class 2606 OID 28389)
-- Dependencies: 1929 1601 1581
-- Name: unidades_id_grupo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY unidades
    ADD CONSTRAINT unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2007 (class 2606 OID 27976)
-- Dependencies: 1606 1975 1602
-- Name: usu_grup_cargo_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2008 (class 2606 OID 27981)
-- Dependencies: 1929 1602 1581
-- Name: usu_grup_cargo_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2006 (class 2606 OID 28291)
-- Dependencies: 1618 1602 1981
-- Name: usu_grup_cargo_ibfk_3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2009 (class 2606 OID 27709)
-- Dependencies: 1599 1959 1603 1603 1599
-- Name: usu_serv_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usu_serv
    ADD CONSTRAINT usu_serv_ibfk_1 FOREIGN KEY (id_serv, id_uni) REFERENCES uni_serv(id_serv, id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2010 (class 2606 OID 27714)
-- Dependencies: 1603 1975 1606
-- Name: usu_serv_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usu_serv
    ADD CONSTRAINT usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 2011 (class 2606 OID 27991)
-- Dependencies: 1604 1606 1975
-- Name: usu_session_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usu_session
    ADD CONSTRAINT usu_session_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;


-- Completed on 2009-02-27 15:05:26 BRT

--
-- PostgreSQL database dump complete
--
