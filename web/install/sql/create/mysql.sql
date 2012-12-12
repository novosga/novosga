-- @adapter=MySQL
-- @author=rogeriolino
-- @date=2012-12-06

--
-- tables
--

CREATE TABLE atend_codif (
    id_atend bigint NOT NULL AUTO_INCREMENT,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (id_atend, id_serv) 
) ENGINE = INNODB;

CREATE TABLE atend_status (
    id_stat integer NOT NULL AUTO_INCREMENT,
    nm_stat varchar(30) NOT NULL,
    desc_stat varchar (150) NOT NULL,
	PRIMARY KEY (`id_stat`)
) ENGINE = INNODB;


CREATE TABLE atendimentos (
    id_atend bigint NOT NULL AUTO_INCREMENT,
    id_uni integer,
    id_usu integer,
    id_serv integer NOT NULL,
    id_pri integer NOT NULL,
    id_stat integer NOT NULL,
    num_senha integer NOT NULL,
    nm_cli varchar(100) DEFAULT NULL,
    num_guiche smallint NOT NULL,
    dt_cheg datetime NOT NULL,
    dt_cha datetime,
    dt_ini datetime,
    dt_fim datetime,
    ident_cli varchar(11) DEFAULT NULL,
    PRIMARY KEY (id_atend)
) ENGINE = INNODB;


CREATE TABLE cargos_aninhados (
    id_cargo integer NOT NULL AUTO_INCREMENT,
    nm_cargo varchar(30) NOT NULL,
    desc_cargo varchar(140),
    esquerda integer NOT NULL,
    direita integer NOT NULL,
    PRIMARY KEY (id_cargo)
) ENGINE = INNODB;


CREATE TABLE cargos_mod_perm (
    id_cargo integer NOT NULL,
    id_mod integer NOT NULL,
    permissao integer NOT NULL,
    PRIMARY KEY (id_cargo, id_mod)
) ENGINE = INNODB;


CREATE TABLE grupos_aninhados (
    id_grupo integer NOT NULL AUTO_INCREMENT,
    nm_grupo varchar(40) NOT NULL,
    desc_grupo varchar(150) NOT NULL,
    esquerda integer NOT NULL,
    direita integer NOT NULL,
    PRIMARY KEY (id_grupo)
) ENGINE = INNODB;


CREATE TABLE historico_atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (id_atend, id_serv)
) ENGINE = INNODB;


CREATE TABLE historico_atendimentos (
    id_atend bigint NOT NULL,
    id_uni integer,
    id_usu integer,
    id_serv integer NOT NULL,
    id_pri integer NOT NULL,
    id_stat integer NOT NULL,
    num_senha integer NOT NULL,
    nm_cli varchar(100) DEFAULT NULL,
    num_guiche smallint NOT NULL,
    dt_cheg datetime NOT NULL,
    dt_cha datetime,
    dt_ini datetime,
    dt_fim datetime,
    ident_cli varchar(11) DEFAULT NULL,
    PRIMARY KEY (id_atend)
) ENGINE = INNODB;

CREATE TABLE modulos (
    id_mod integer NOT NULL AUTO_INCREMENT,
    chave_mod varchar(50) NOT NULL,
    nm_mod varchar(25) NOT NULL,
    desc_mod varchar(100) NOT NULL,
    autor_mod varchar(25) NOT NULL,
    img_mod varchar(150) DEFAULT NULL,
    tipo_mod smallint NOT NULL,
    stat_mod smallint NOT NULL,
    PRIMARY KEY (id_mod)
) ENGINE = INNODB;

CREATE TABLE paineis (
    id_uni integer NOT NULL,
    host integer NOT NULL,
    PRIMARY KEY (host)
) ENGINE = INNODB;

CREATE TABLE paineis_servicos (
    host integer NOT NULL,
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    PRIMARY KEY (host, id_serv)
) ENGINE = INNODB;


CREATE TABLE painel_senha (
    contador integer NOT NULL AUTO_INCREMENT,
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    num_senha integer NOT NULL,
    sig_senha char(1) NOT NULL,
    msg_senha varchar(15) NOT NULL,
    nm_local varchar(15) NOT NULL,
    num_guiche smallint NOT NULL,
    PRIMARY KEY (contador)
) ENGINE = INNODB;


CREATE TABLE prioridades (
    id_pri integer NOT NULL AUTO_INCREMENT,
    nm_pri varchar(30) NOT NULL,
    desc_pri varchar(100) NOT NULL,
    peso_pri smallint NOT NULL,
    stat_pri smallint NOT NULL,
    PRIMARY KEY (id_pri)
) ENGINE = INNODB;

CREATE TABLE serv_local (
    id_loc integer NOT NULL AUTO_INCREMENT,
    nm_loc varchar(20) NOT NULL,
    PRIMARY KEY (id_loc)
) ENGINE = INNODB;

CREATE TABLE serv_peso (
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (id_serv)
) ENGINE = INNODB;

CREATE TABLE servicos (
    id_serv integer NOT NULL AUTO_INCREMENT,
    id_macro integer,
    desc_serv varchar(100) NOT NULL,
    nm_serv varchar(50),
    stat_serv smallint,
    PRIMARY KEY (id_serv)
) ENGINE = INNODB;

CREATE TABLE uni_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_loc integer NOT NULL,
    nm_serv varchar(50) NOT NULL,
    sigla_serv char(1) NOT NULL,
    stat_serv smallint NOT NULL,
    PRIMARY KEY (id_uni, id_serv)
) ENGINE = INNODB;


CREATE TABLE unidades (
    id_uni integer NOT NULL AUTO_INCREMENT,
    id_grupo integer NOT NULL,
    cod_uni varchar(10) NOT NULL,
    nm_uni varchar(50) DEFAULT NULL,
    stat_uni smallint DEFAULT 1,
    stat_imp smallint DEFAULT 0,
    msg_imp varchar(100),
    PRIMARY KEY (id_uni)
) ENGINE = INNODB;


CREATE TABLE usu_grup_cargo (
    id_usu integer NOT NULL,
    id_grupo integer NOT NULL,
    id_cargo integer NOT NULL,
    PRIMARY KEY (id_usu, id_grupo)
) ENGINE = INNODB;


CREATE TABLE usu_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_usu integer NOT NULL,
    PRIMARY KEY (id_uni, id_serv, id_usu)
) ENGINE = INNODB;

CREATE TABLE usu_session (
    id_usu integer NOT NULL,
    session_id varchar(40) NOT NULL,
    stat_session integer NOT NULL,
    PRIMARY KEY (id_usu)
) ENGINE = INNODB;

CREATE TABLE usuarios (
    id_usu integer NOT NULL AUTO_INCREMENT,
    login_usu varchar(20) NOT NULL,
    nm_usu varchar(20) NOT NULL,
    ult_nm_usu varchar(100) NOT NULL,
    senha_usu varchar(40) NOT NULL,
    ult_acesso datetime,
    stat_usu smallint NOT NULL,
    PRIMARY KEY (id_usu)
) ENGINE = INNODB;

--
-- keys
--

ALTER TABLE atend_codif ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE atend_codif ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE paineis ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE painel_senha ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE painel_senha ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE serv_peso ADD CONSTRAINT peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE servicos ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE unidades ADD CONSTRAINT unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE usu_serv ADD CONSTRAINT usu_serv_ibfk_1 FOREIGN KEY (id_serv, id_uni) REFERENCES uni_serv(id_serv, id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE usu_serv ADD CONSTRAINT usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE usu_session ADD CONSTRAINT usu_session_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- indexes
--

CREATE UNIQUE INDEX cod_uni ON unidades (cod_uni);

CREATE INDEX direita ON grupos_aninhados (direita);

CREATE INDEX esqdir ON grupos_aninhados (esquerda, direita);

CREATE INDEX esquerda ON grupos_aninhados (esquerda);

CREATE INDEX fki_atend_codif_ibfk_2 ON atend_codif (id_serv);

CREATE INDEX fki_atendimentos_ibfk_1 ON atendimentos (id_pri);

CREATE INDEX fki_atendimentos_ibfk_2 ON atendimentos (id_uni, id_serv);

CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos (id_stat);

CREATE INDEX fki_atendimentos_ibfk_4 ON atendimentos (id_usu);

CREATE INDEX fki_id_grupo ON unidades (id_grupo);

CREATE INDEX fki_servicos_ibfk_1 ON servicos (id_macro);

CREATE INDEX fki_uni_serv_ibfk_2 ON uni_serv (id_serv);

CREATE INDEX fki_uni_serv_ibfk_3 ON uni_serv (id_loc);

CREATE INDEX fki_usu_serv_ibfk_1 ON usu_serv (id_serv, id_uni);

CREATE INDEX fki_usu_serv_ibfk_2 ON usu_serv (id_usu);

CREATE UNIQUE INDEX local_serv_nm ON serv_local (nm_loc);

CREATE UNIQUE INDEX login_usu ON usuarios (login_usu);

CREATE UNIQUE INDEX modulos_chave ON modulos (chave_mod);


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
