DO $body$
BEGIN

CREATE TABLE agendamentos (
    id SERIAL PRIMARY KEY,
    cliente_id INT,
    unidade_id INT,
    servico_id INT,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    data_confirmacao TIMESTAMP,
    CONSTRAINT fk_agendamentos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_agendamentos_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    CONSTRAINT fk_agendamentos_servico FOREIGN KEY (servico_id) REFERENCES servicos(id)
);

CREATE TABLE atendimentos (
    id SERIAL PRIMARY KEY,
    cliente_id INT,
    unidade_id INT,
    servico_id INT,
    prioridade_id INT,
    usuario_id INT,
    usuario_tri_id INT,
    atendimento_id INT,
    num_local SMALLINT,
    dt_age TIMESTAMP,
    dt_cheg TIMESTAMP NOT NULL,
    dt_cha TIMESTAMP,
    dt_ini TIMESTAMP,
    dt_fim TIMESTAMP,
    tempo_espera INT,
    tempo_permanencia INT,
    tempo_atendimento INT,
    tempo_deslocamento INT,
    status VARCHAR(25) NOT NULL,
    resolucao VARCHAR(25),
    observacao TEXT,
    senha_sigla VARCHAR(3) NOT NULL,
    senha_numero INT NOT NULL,
    CONSTRAINT fk_atendimentos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_atendimentos_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    CONSTRAINT fk_atendimentos_servico FOREIGN KEY (servico_id) REFERENCES servicos(id),
    CONSTRAINT fk_atendimentos_prioridade FOREIGN KEY (prioridade_id) REFERENCES prioridades(id),
    CONSTRAINT fk_atendimentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_atendimentos_usuario_tri FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id),
    CONSTRAINT fk_atendimentos_atendimento FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id)
);

CREATE TABLE atendimentos_codificados (
    servico_id INT NOT NULL,
    atendimento_id INT NOT NULL,
    valor_peso SMALLINT NOT NULL,
    PRIMARY KEY (servico_id, atendimento_id),
    CONSTRAINT fk_atendimentos_codificados_servico FOREIGN KEY (servico_id) REFERENCES servicos(id),
    CONSTRAINT fk_atendimentos_codificados_atendimento FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id)
);

CREATE TABLE atendimentos_metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    atendimento_id INTEGER NOT NULL,
    value JSON NOT NULL,
    PRIMARY KEY (namespace, name, atendimento_id),
    CONSTRAINT idx_atendimentos_metadata_atendimento_id FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id)
);

CREATE TABLE clientes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(60) NOT NULL,
    documento VARCHAR(30) NOT NULL,
    email VARCHAR(80)
);

CREATE TABLE clientes_metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    cliente_id INTEGER NOT NULL,
    value JSONB NOT NULL,
    PRIMARY KEY (namespace, name, cliente_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

CREATE TABLE contador (
    unidade_id INTEGER NOT NULL,
    servico_id INTEGER NOT NULL,
    numero INTEGER,
    PRIMARY KEY (unidade_id, servico_id)
);

CREATE TABLE departamentos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    descricao VARCHAR(250) NOT NULL,
    ativo BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE historico_atendimentos (
    id SERIAL PRIMARY KEY,
    cliente_id INTEGER,
    unidade_id INTEGER,
    servico_id INTEGER,
    prioridade_id INTEGER,
    usuario_id INTEGER,
    usuario_tri_id INTEGER,
    atendimento_id INTEGER,
    num_local SMALLINT,
    dt_age TIMESTAMP,
    dt_cheg TIMESTAMP NOT NULL,
    dt_cha TIMESTAMP,
    dt_ini TIMESTAMP,
    dt_fim TIMESTAMP,
    tempo_espera INTEGER,
    tempo_permanencia INTEGER,
    tempo_atendimento INTEGER,
    tempo_deslocamento INTEGER,
    status VARCHAR(25) NOT NULL,
    resolucao VARCHAR(25),
    observacao TEXT,
    senha_sigla VARCHAR(3) NOT NULL,
    senha_numero INTEGER NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (unidade_id) REFERENCES contador(unidade_id),
    FOREIGN KEY (servico_id) REFERENCES contador(servico_id),
    FOREIGN KEY (prioridade_id) REFERENCES prioridades(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id),
    FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id)
);

CREATE TABLE historico_atendimentos_codificados (
    servico_id INTEGER NOT NULL,
    atendimento_id INTEGER NOT NULL,
    valor_peso SMALLINT NOT NULL,
    PRIMARY KEY (servico_id, atendimento_id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id)
);

CREATE TABLE historico_atendimentos_metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    atendimento_id INTEGER NOT NULL,
    value JSONB NOT NULL,
    PRIMARY KEY (namespace, name, atendimento_id),
    FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id)
);

CREATE TABLE locais (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL,
    UNIQUE (nome)
);

CREATE TABLE lotacoes (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER,
    unidade_id INTEGER,
    perfil_id INTEGER,
    UNIQUE (usuario_id, unidade_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    FOREIGN KEY (perfil_id) REFERENCES perfis(id)
);

CREATE TABLE metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    value JSONB NOT NULL,
    PRIMARY KEY (namespace, name)
);

CREATE TABLE oauth_access_tokens (
    id SERIAL PRIMARY KEY,
    client_id INTEGER,
    user_id INTEGER,
    token VARCHAR(255) NOT NULL,
    expires_at INTEGER,
    scope VARCHAR(255),
    UNIQUE (token),
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id),
    FOREIGN KEY (user_id) REFERENCES usuarios(id)
);

CREATE TABLE oauth_clients (
    id SERIAL PRIMARY KEY,
    random_id VARCHAR(255) NOT NULL,
    redirect_uris TEXT[] NOT NULL,
    secret VARCHAR(255) NOT NULL,
    allowed_grant_types TEXT[] NOT NULL,
    description VARCHAR(30) NOT NULL
);

CREATE TABLE oauth_refresh_tokens (
    id SERIAL PRIMARY KEY,
    client_id INTEGER,
    user_id INTEGER,
    token VARCHAR(255) NOT NULL,
    expires_at INTEGER,
    scope VARCHAR(255),
    UNIQUE (token),
    FOREIGN KEY (client_id) REFERENCES oauth_clients(id),
    FOREIGN KEY (user_id) REFERENCES usuarios(id)
);

CREATE TABLE paineis (
    host INTEGER PRIMARY KEY,
    unidade_id INTEGER,
    senha VARCHAR(128),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
);

CREATE TABLE paineis_servicos (
    host INTEGER NOT NULL,
    servico_id INTEGER NOT NULL,
    unidade_id INTEGER,
    PRIMARY KEY (host, servico_id),
    FOREIGN KEY (host) REFERENCES paineis(host),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
);

CREATE TABLE painel_senha (
    id SERIAL PRIMARY KEY,
    servico_id INTEGER,
    unidade_id INTEGER,
    num_senha INTEGER NOT NULL,
    sig_senha VARCHAR(3) NOT NULL,
    msg_senha VARCHAR(255) NOT NULL,
    local VARCHAR(20) NOT NULL,
    num_local SMALLINT NOT NULL,
    peso SMALLINT NOT NULL,
    prioridade VARCHAR(100),
    nome_cliente VARCHAR(100),
    documento_cliente VARCHAR(30),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
);

CREATE TABLE perfis (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(150) NOT NULL,
    modulos TEXT[] COMMENT '(DC2Type:simple_array)',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE prioridades (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(64) NOT NULL,
    descricao VARCHAR(100) NOT NULL,
    peso SMALLINT NOT NULL,
    ativo BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL,
    deleted_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE servicos (
    id SERIAL PRIMARY KEY,
    macro_id INTEGER,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(250) NOT NULL,
    ativo BOOLEAN NOT NULL,
    peso SMALLINT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL,
    deleted_at TIMESTAMP DEFAULT NULL,
    FOREIGN KEY (macro_id) REFERENCES servicos(id)
);

CREATE TABLE servicos_metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    servico_id INTEGER NOT NULL,
    value JSONB NOT NULL,
    PRIMARY KEY (namespace, name, servico_id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id)
);

CREATE TABLE servicos_unidades (
    servico_id INTEGER NOT NULL,
    unidade_id INTEGER NOT NULL,
    local_id INTEGER,
    departamento_id INTEGER,
    sigla VARCHAR(3) NOT NULL,
    ativo BOOLEAN NOT NULL,
    peso SMALLINT NOT NULL,
    prioridade BOOLEAN NOT NULL,
    numero_inicial INTEGER NOT NULL,
    numero_final INTEGER,
    incremento INTEGER NOT NULL,
    mensagem VARCHAR(255),
    PRIMARY KEY (servico_id, unidade_id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    FOREIGN KEY (local_id) REFERENCES locais(id),
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
);

CREATE TABLE servicos_usuarios (
    servico_id INTEGER NOT NULL,
    unidade_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    peso SMALLINT NOT NULL,
    PRIMARY KEY (servico_id, unidade_id, usuario_id),
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE unidades (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(250) NOT NULL,
    ativo BOOLEAN NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL,
    deleted_at TIMESTAMP DEFAULT NULL,
    impressao_cabecalho VARCHAR(150) NOT NULL,
    impressao_rodape VARCHAR(150) NOT NULL,
    impressao_exibir_data BOOLEAN NOT NULL,
    impressao_exibir_prioridade BOOLEAN NOT NULL,
    impressao_exibir_nome_unidade BOOLEAN NOT NULL,
    impressao_exibir_nome_servico BOOLEAN NOT NULL,
    impressao_exibir_mensagem_servico BOOLEAN NOT NULL
);

CREATE TABLE unidades_metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    unidade_id INTEGER NOT NULL,
    value JSONB NOT NULL,
    PRIMARY KEY (namespace, name, unidade_id),
    FOREIGN KEY (unidade_id) REFERENCES unidades(id)
);

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    login VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(20) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    senha VARCHAR(128) NOT NULL,
    ativo BOOLEAN NOT NULL,
    ultimo_acesso TIMESTAMP DEFAULT NULL,
    ip VARCHAR(15) DEFAULT NULL,
    session_id VARCHAR(50) DEFAULT NULL,
    algorithm VARCHAR(10) NOT NULL,
    admin BOOLEAN NOT NULL,
    salt VARCHAR(60) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT NULL,
    deleted_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE usuarios_metadata (
    namespace VARCHAR(30) NOT NULL,
    name VARCHAR(30) NOT NULL,
    usuario_id INTEGER NOT NULL,
    value JSONB NOT NULL,
    PRIMARY KEY (namespace, name, usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

ALTER TABLE agendamentos ADD CONSTRAINT FK_2D12EA4A82E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE agendamentos ADD CONSTRAINT FK_2D12EA4ADE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes(id);
ALTER TABLE agendamentos ADD CONSTRAINT FK_2D12EA4AEDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E7226EFC79 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id);
ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E776323123 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id);
ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E782E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E7875F1A79 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id);
ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E7DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios(id);
ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E7DE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes(id);
ALTER TABLE atendimentos ADD CONSTRAINT FK_29E906E7EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE atendimentos_codificados ADD CONSTRAINT FK_DDF47B2D76323123 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id);
ALTER TABLE atendimentos_codificados ADD CONSTRAINT FK_DDF47B2D82E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);

ALTER TABLE atendimentos_metadata ADD CONSTRAINT FK_4F7723EB76323123 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id);

ALTER TABLE clientes_metadata ADD CONSTRAINT FK_23B81DEEDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes(id);

ALTER TABLE contador ADD CONSTRAINT FK_E83EF8FA82E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE contador ADD CONSTRAINT FK_E83EF8FAEDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95F226EFC79 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id);
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95F76323123 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id);
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95F82E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95F875F1A79 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id);
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95FDB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios(id);
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95FDE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes(id);
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_CBBDF95FEDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE historico_atendimentos_codificados ADD CONSTRAINT FK_111248C276323123 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id);
ALTER TABLE historico_atendimentos_codificados ADD CONSTRAINT FK_111248C282E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);

ALTER TABLE historico_atendimentos_metadata ADD CONSTRAINT FK_169630A576323123 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id);

ALTER TABLE lotacoes ADD CONSTRAINT FK_10E72C2F57291544 FOREIGN KEY (perfil_id) REFERENCES perfis(id);
ALTER TABLE lotacoes ADD CONSTRAINT FK_10E72C2FDB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios(id);
ALTER TABLE lotacoes ADD CONSTRAINT FK_10E72C2FEDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients(id);
ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527CA76ED395 FOREIGN KEY (user_id) REFERENCES usuarios(id);

ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB68719EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients(id);
ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB687A76ED395 FOREIGN KEY (user_id) REFERENCES usuarios(id);

ALTER TABLE paineis ADD CONSTRAINT FK_CE58BF05EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE paineis_servicos ADD CONSTRAINT FK_D98415D382E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE paineis_servicos ADD CONSTRAINT FK_D98415D3CF2713FD FOREIGN KEY (host) REFERENCES paineis(host);
ALTER TABLE paineis_servicos ADD CONSTRAINT FK_D98415D3EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE painel_senha ADD CONSTRAINT FK_390182E6EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id),
ALTER TABLE painel_senha ADD CONSTRAINT FK_390182E682E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);

ALTER TABLE servicos ADD CONSTRAINT FK_89DD09E3F43A187E FOREIGN KEY (macro_id) REFERENCES servicos(id);

ALTER TABLE servicos_metadata ADD CONSTRAINT FK_8E8BF0E482E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);

ALTER TABLE servicos_unidades ADD CONSTRAINT FK_C50F70345A91C08D FOREIGN KEY (departamento_id) REFERENCES departamentos(id);
ALTER TABLE servicos_unidades ADD CONSTRAINT FK_C50F70345D5A2101 FOREIGN KEY (local_id) REFERENCES locais(id);
ALTER TABLE servicos_unidades ADD CONSTRAINT FK_C50F703482E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE servicos_unidades ADD CONSTRAINT FK_C50F7034EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE servicos_usuarios ADD CONSTRAINT FK_CF69430282E14982 FOREIGN KEY (servico_id) REFERENCES servicos(id);
ALTER TABLE servicos_usuarios ADD CONSTRAINT FK_CF694302DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios(id);
ALTER TABLE servicos_usuarios ADD CONSTRAINT FK_CF694302EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE unidades_metadata ADD CONSTRAINT FK_A21ACF47EDF4B99B FOREIGN KEY (unidade_id) REFERENCES unidades(id);

ALTER TABLE usuarios_metadata ADD CONSTRAINT FK_BD8E7838DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios(id);

CREATE VIEW view_atendimentos AS
    SELECT
        atendimentos.id AS id,
        atendimentos.num_local AS num_local,
        atendimentos.dt_age AS dt_age,
        atendimentos.dt_cheg AS dt_cheg,
        atendimentos.dt_cha AS dt_cha,
        atendimentos.dt_ini AS dt_ini,
        atendimentos.dt_fim AS dt_fim,
        atendimentos.tempo_espera AS tempo_espera,
        atendimentos.tempo_permanencia AS tempo_permanencia,
        atendimentos.tempo_atendimento AS tempo_atendimento,
        atendimentos.tempo_deslocamento AS tempo_deslocamento,
        atendimentos.status AS status,
        atendimentos.resolucao AS resolucao,
        atendimentos.observacao AS observacao,
        atendimentos.senha_sigla AS senha_sigla,
        atendimentos.senha_numero AS senha_numero,
        atendimentos.cliente_id AS cliente_id,
        atendimentos.unidade_id AS unidade_id,
        atendimentos.servico_id AS servico_id,
        atendimentos.prioridade_id AS prioridade_id,
        atendimentos.usuario_id AS usuario_id,
        atendimentos.usuario_tri_id AS usuario_tri_id,
        atendimentos.atendimento_id AS atendimento_id
    FROM
        atendimentos
    UNION ALL
    SELECT
        historico_atendimentos.id AS id,
        historico_atendimentos.num_local AS num_local,
        historico_atendimentos.dt_age AS dt_age,
        historico_atendimentos.dt_cheg AS dt_cheg,
        historico_atendimentos.dt_cha AS dt_cha,
        historico_atendimentos.dt_ini AS dt_ini,
        historico_atendimentos.dt_fim AS dt_fim,
        historico_atendimentos.tempo_espera AS tempo_espera,
        historico_atendimentos.tempo_permanencia AS tempo_permanencia,
        historico_atendimentos.tempo_atendimento AS tempo_atendimento,
        historico_atendimentos.tempo_deslocamento AS tempo_deslocamento,
        historico_atendimentos.status AS status,
        historico_atendimentos.resolucao AS resolucao,
        historico_atendimentos.observacao AS observacao,
        historico_atendimentos.senha_sigla AS senha_sigla,
        historico_atendimentos.senha_numero AS senha_numero,
        historico_atendimentos.cliente_id AS cliente_id,
        historico_atendimentos.unidade_id AS unidade_id,
        historico_atendimentos.servico_id AS servico_id,
        historico_atendimentos.prioridade_id AS prioridade_id,
        historico_atendimentos.usuario_id AS usuario_id,
        historico_atendimentos.usuario_tri_id AS usuario_tri_id,
        historico_atendimentos.atendimento_id AS atendimento_id
    FROM
        historico_atendimentos;

CREATE VIEW view_atendimentos_codificados AS
    SELECT
        atendimentos_codificados.valor_peso AS valor_peso,
        atendimentos_codificados.servico_id AS servico_id,
        atendimentos_codificados.atendimento_id AS atendimento_id
    FROM
        atendimentos_codificados
    UNION ALL
    SELECT
        historico_atendimentos_codificados.valor_peso AS valor_peso,
        historico_atendimentos_codificados.servico_id AS servico_id,
        historico_atendimentos_codificados.atendimento_id AS atendimento_id
    FROM 
        historico_atendimentos_codificados;

END $body$;
