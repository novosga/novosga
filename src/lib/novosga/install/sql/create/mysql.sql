-- @adapter=MySQL / MariaDB
-- @author=rogeriolino
-- @date=2012-12-06

--
-- tables
--

CREATE TABLE atend_codif (
    atendimento_id bigint NOT NULL AUTO_INCREMENT,
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (atendimento_id, servico_id) 
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE atendimentos (
    id bigint NOT NULL AUTO_INCREMENT,
    unidade_id integer,
    usuario_id integer,
    usuario_tri_id integer NOT NULL,
    servico_id integer NOT NULL,
    prioridade_id integer NOT NULL,
    atendimento_id bigint,
    status integer NOT NULL,
    sigla_senha char(1) NOT NULL,
    num_senha integer NOT NULL,
    num_senha_serv integer NOT NULL,
    nm_cli varchar(100) DEFAULT NULL,
    num_local smallint NOT NULL,
    dt_cheg datetime NOT NULL,
    dt_cha datetime,
    dt_ini datetime,
    dt_fim datetime,
    ident_cli varchar(11) DEFAULT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;


CREATE TABLE cargos (
    id integer NOT NULL AUTO_INCREMENT,
    nome varchar(30) NOT NULL,
    descricao varchar(140),
    esquerda integer NOT NULL,
    direita integer NOT NULL,
    nivel integer NOT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE cargos_mod_perm (
    cargo_id integer NOT NULL,
    modulo_id integer NOT NULL,
    permissao integer NOT NULL,
    PRIMARY KEY (cargo_id, modulo_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE grupos (
    id integer NOT NULL AUTO_INCREMENT,
    nome varchar(40) NOT NULL,
    descricao varchar(150) NOT NULL,
    esquerda integer NOT NULL,
    direita integer NOT NULL,
    nivel integer NOT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE config (
    chave varchar(150) NOT NULL,
    valor TEXT NOT NULL,
    tipo integer NOT NULL,
    PRIMARY KEY (chave)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;


CREATE TABLE historico_atend_codif (
    atendimento_id bigint NOT NULL,
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (atendimento_id, servico_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;


CREATE TABLE historico_atendimentos (
    id bigint NOT NULL,
    unidade_id integer,
    usuario_id integer,
    usuario_tri_id integer NOT NULL,
    servico_id integer NOT NULL,
    prioridade_id integer NOT NULL,
    status integer NOT NULL,
    sigla_senha char(1) NOT NULL,
    num_senha integer NOT NULL,
    num_senha_serv integer NOT NULL,
    nm_cli varchar(100) DEFAULT NULL,
    num_local smallint NOT NULL,
    dt_cheg datetime NOT NULL,
    dt_cha datetime,
    dt_ini datetime,
    dt_fim datetime,
    ident_cli varchar(11) DEFAULT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE modulos (
    id integer NOT NULL AUTO_INCREMENT,
    chave varchar(50) NOT NULL,
    nome varchar(25) NOT NULL,
    descricao varchar(100) NOT NULL,
    autor varchar(25) NOT NULL,
    tipo smallint NOT NULL,
    status smallint NOT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE paineis (
    unidade_id integer NOT NULL,
    host integer NOT NULL,
    PRIMARY KEY (host)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE paineis_servicos (
    host integer NOT NULL,
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    PRIMARY KEY (host, servico_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE painel_senha (
    id integer NOT NULL AUTO_INCREMENT,
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    num_senha integer NOT NULL,
    sig_senha char(1) NOT NULL,
    msg_senha varchar(20) NOT NULL,
    local varchar(15) NOT NULL,
    num_local smallint NOT NULL,
    dt_envio timestamp NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE prioridades (
    id integer NOT NULL AUTO_INCREMENT,
    nome varchar(30) NOT NULL,
    descricao varchar(100) NOT NULL,
    peso smallint NOT NULL,
    status smallint NOT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE locais (
    id integer NOT NULL AUTO_INCREMENT,
    nome varchar(20) NOT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE serv_peso (
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (servico_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE servicos (
    id integer NOT NULL AUTO_INCREMENT,
    id_macro integer,
    descricao varchar(100) NOT NULL,
    nome varchar(50),
    status smallint,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE uni_serv (
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    local_id integer NOT NULL,
    nome varchar(50) NOT NULL,
    sigla char(1) NOT NULL,
    status smallint NOT NULL,
    PRIMARY KEY (unidade_id, servico_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;


CREATE TABLE unidades (
    id integer NOT NULL AUTO_INCREMENT,
    grupo_id integer NOT NULL,
    codigo varchar(10) NOT NULL,
    nome varchar(50) DEFAULT NULL,
    status smallint DEFAULT 1,
    stat_imp smallint DEFAULT 0,
    msg_imp varchar(100),
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;


CREATE TABLE usu_grup_cargo (
    usuario_id integer NOT NULL,
    grupo_id integer NOT NULL,
    cargo_id integer NOT NULL,
    PRIMARY KEY (usuario_id, grupo_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE usu_serv (
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    usuario_id integer NOT NULL,
    PRIMARY KEY (unidade_id, servico_id, usuario_id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE usuarios (
    id integer NOT NULL AUTO_INCREMENT,
    login varchar(20) NOT NULL,
    nome varchar(20) NOT NULL,
    sobrenome varchar(100) NOT NULL,
    senha varchar(40) NOT NULL,
    ult_acesso datetime,
    status smallint NOT NULL,
    session_id varchar(40) NOT NULL,
    PRIMARY KEY (id)
) 
DEFAULT CHARACTER SET utf8   
COLLATE utf8_general_ci
ENGINE = INNODB;

--
-- keys
--

ALTER TABLE atend_codif ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atend_codif ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_6 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE paineis ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv (unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE painel_senha ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE painel_senha ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE serv_peso ADD CONSTRAINT peso_ibfk_1 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE servicos ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (local_id) REFERENCES locais(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE unidades ADD CONSTRAINT unidades_grupo_id_fkey FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_2 FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE usu_grup_cargo ADD CONSTRAINT usu_grup_cargo_ibfk_3 FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE usu_serv ADD CONSTRAINT usu_serv_ibfk_1 FOREIGN KEY (servico_id, unidade_id) REFERENCES uni_serv(servico_id, unidade_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE usu_serv ADD CONSTRAINT usu_serv_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;

--
-- indexes
--

CREATE UNIQUE INDEX codigo ON unidades (codigo);
CREATE INDEX direita ON grupos (direita);
CREATE INDEX esqdir ON grupos (esquerda, direita);
CREATE INDEX esquerda ON grupos (esquerda);
CREATE INDEX fki_atend_codif_ibfk_2 ON atend_codif (servico_id);
CREATE INDEX fki_atendimentos_ibfk_1 ON atendimentos (prioridade_id);
CREATE INDEX fki_atendimentos_ibfk_2 ON atendimentos (unidade_id, servico_id);
CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos (status);
CREATE INDEX fki_atendimentos_ibfk_4 ON atendimentos (usuario_id);
CREATE INDEX fki_grupo_id ON unidades (grupo_id);
CREATE INDEX fki_servicos_ibfk_1 ON servicos (id_macro);
CREATE INDEX fki_uni_serv_ibfk_2 ON uni_serv (servico_id);
CREATE INDEX fki_uni_serv_ibfk_3 ON uni_serv (local_id);
CREATE INDEX fki_usu_serv_ibfk_1 ON usu_serv (servico_id, unidade_id);
CREATE INDEX fki_usu_serv_ibfk_2 ON usu_serv (usuario_id);
CREATE UNIQUE INDEX local_serv_nm ON locais (nome);
CREATE UNIQUE INDEX login ON usuarios (login);
CREATE UNIQUE INDEX modulos_chave ON modulos (chave);


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
