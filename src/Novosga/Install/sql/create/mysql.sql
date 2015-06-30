-- @adapter=MySQL / MariaDB
-- @author=Rogério Lino

--
-- tables
--

CREATE TABLE contador (
    unidade_id INT NOT NULL,
    total INT NOT NULL DEFAULT 0,
    PRIMARY KEY(unidade_id)
);

CREATE TABLE atend_codif (
    atendimento_id bigint NOT NULL AUTO_INCREMENT,
    servico_id integer NOT NULL,
    valor_peso smallint NOT NULL,
    PRIMARY KEY (atendimento_id, servico_id)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = MyISAM;

CREATE TABLE atend_meta (
    atendimento_id bigint NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (atendimento_id, name)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = MyISAM;

CREATE TABLE atendimentos (
    id bigint NOT NULL AUTO_INCREMENT,
    unidade_id integer NOT NULL,
    usuario_id integer,
    usuario_tri_id integer NOT NULL,
    servico_id integer NOT NULL,
    prioridade_id integer NOT NULL,
    atendimento_id bigint,
    status smallint NOT NULL,
    sigla_senha varchar(1) NOT NULL,
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
ENGINE = MyISAM;


CREATE TABLE cargos (
    id integer NOT NULL AUTO_INCREMENT,
    nome varchar(50) NOT NULL,
    descricao varchar(150) NOT NULL,
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
    nome varchar(50) NOT NULL,
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
ENGINE = MyISAM;

CREATE TABLE historico_atend_meta (
    atendimento_id bigint NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (atendimento_id, name)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = MyISAM;


CREATE TABLE historico_atendimentos (
    id bigint NOT NULL,
    unidade_id integer,
    usuario_id integer,
    usuario_tri_id integer NOT NULL,
    servico_id integer NOT NULL,
    prioridade_id integer NOT NULL,
    atendimento_id bigint,
    status integer NOT NULL,
    sigla_senha varchar(1) NOT NULL,
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
ENGINE = MyISAM;

CREATE TABLE modulos (
    id integer NOT NULL AUTO_INCREMENT,
    chave varchar(50) NOT NULL,
    nome varchar(25) NOT NULL,
    descricao varchar(100) NOT NULL,
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
    sig_senha varchar(1) NOT NULL,
    msg_senha varchar(20) NOT NULL,
    local varchar(15) NOT NULL,
    num_local smallint NOT NULL,
    peso smallint NOT NULL,
    prioridade VARCHAR(100),
    nome_cliente VARCHAR(100),
    documento_cliente VARCHAR(30),
    PRIMARY KEY (id)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE prioridades (
    id integer NOT NULL AUTO_INCREMENT,
    nome varchar(64) NOT NULL,
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

CREATE TABLE servicos (
    id integer NOT NULL AUTO_INCREMENT,
    macro_id integer,
    descricao varchar(100) NOT NULL,
    nome varchar(50) NOT NULL,
    status smallint NOT NULL,
    peso smallint NOT NULL,
    PRIMARY KEY (id)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE serv_meta (
    servico_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT ,
    PRIMARY KEY (servico_id, name)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = MyISAM;

CREATE TABLE uni_serv (
    unidade_id integer NOT NULL,
    servico_id integer NOT NULL,
    local_id integer NOT NULL,
    sigla varchar(1) NOT NULL,
    status smallint NOT NULL,
    peso smallint NOT NULL,
    PRIMARY KEY (unidade_id, servico_id)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = INNODB;


CREATE TABLE unidades (
    id integer NOT NULL AUTO_INCREMENT,
    grupo_id integer NOT NULL,
    codigo varchar(10) NOT NULL,
    nome varchar(50) NOT NULL,
    status smallint NOT NULL,
    stat_imp smallint NOT NULL,
    msg_imp varchar(100) NOT NULL,
    PRIMARY KEY (id)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE uni_meta (
    unidade_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT ,
    PRIMARY KEY (unidade_id, name)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = MyISAM;

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
    senha varchar(60) NOT NULL,
    ult_acesso datetime,
    status smallint NOT NULL,
    session_id varchar(50) NULL,
    PRIMARY KEY (id)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = INNODB;

CREATE TABLE usu_meta (
    usuario_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT,
    PRIMARY KEY (usuario_id, name)
)
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci
ENGINE = MyISAM;

-- oauth2

CREATE TABLE oauth_clients (
    client_id VARCHAR(80) NOT NULL,
    client_secret VARCHAR(80) NOT NULL,
    redirect_uri VARCHAR(2000) NOT NULL,
    grant_types VARCHAR(80),
    scope VARCHAR(100),
    user_id VARCHAR(80),
    PRIMARY KEY (client_id)
);

CREATE TABLE oauth_scopes (
    scope TEXT,
    is_default BOOLEAN
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

--
-- keys
--

ALTER TABLE contador ADD FOREIGN KEY (unidade_id) REFERENCES unidades (id);
ALTER TABLE atend_codif ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atend_codif ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atend_meta ADD CONSTRAINT atend_meta_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE atendimentos ADD CONSTRAINT atendimentos_ibfk_6 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE cargos_mod_perm ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atend_codif ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atend_meta ADD CONSTRAINT historico_atend_meta_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE historico_atendimentos ADD CONSTRAINT historico_atendimentos_ibfk_6 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE paineis ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE paineis_servicos ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv (unidade_id, servico_id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE painel_senha ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE painel_senha ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE servicos ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (macro_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE serv_meta ADD FOREIGN KEY (servico_id) REFERENCES servicos (id);
ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE uni_serv ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (local_id) REFERENCES locais(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE unidades ADD CONSTRAINT unidades_grupo_id_fkey FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON UPDATE RESTRICT ON DELETE RESTRICT;
ALTER TABLE uni_meta ADD FOREIGN KEY (unidade_id) REFERENCES unidades (id);
ALTER TABLE usu_meta ADD FOREIGN KEY (usuario_id) REFERENCES usuarios (id);
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
CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos (status);
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
