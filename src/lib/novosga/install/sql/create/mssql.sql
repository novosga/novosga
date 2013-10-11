-- @adapter=MS SQL (experimental)
-- @author=rogeriolino
-- @date=2013-08-29

CREATE TABLE [dbo].[atend_codif](
    [atendimento_id] [bigint] NOT NULL,
    [servico_id] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (atendimento_id, servico_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[atendimentos](
    [id] [bigint] identity(1,1) NOT NULL,
    [unidade_id] [int] NULL,
    [usuario_id] [int] NULL,
    [usuario_tri_id] [int] NOT NULL,
    [servico_id] [int] NOT NULL,
    [prioridade_id] [int] NOT NULL,
    [status] [int] NOT NULL,
    [sigla_senha] [char](1) NOT NULL,
    [num_senha] [int] NOT NULL,
    [num_senha_serv] [int] NOT NULL,
    [nm_cli] [varchar](100) NULL,
    [num_guiche] [smallint] NOT NULL,
    [dt_cheg] [datetime2](6) NOT NULL,
    [dt_cha] [datetime2](6) NULL,
    [dt_ini] [datetime2](6) NULL,
    [dt_fim] [datetime2](6) NULL,
    [ident_cli] [varchar](11) NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[cargos](
    [id] [int] identity(1,1) NOT NULL,
    [nome] [varchar](30) NOT NULL,
    [descricao] [varchar](140) NULL,
    [esquerda] [int] NOT NULL,
    [direita] [int] NOT NULL,
    [nivel] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[cargos_mod_perm](
    [cargo_id] [int] NOT NULL,
    [modulo_id] [int] NOT NULL,
    [permissao] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (cargo_id, modulo_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[grupos](
    [id] [int] identity(1,1) NOT NULL,
    [nome] [varchar](40) NOT NULL,
    [descricao] [varchar](150) NOT NULL,
    [esquerda] [int] NOT NULL,
    [direita] [int] NOT NULL,
    [nivel] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[config](
    [chave] [varchar](150) NOT NULL,
    [valor] [text] NOT NULL,
    [tipo] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (chave)
) ON [PRIMARY]


CREATE TABLE [dbo].[historico_atend_codif](
    [atendimento_id] [bigint] NOT NULL,
    [servico_id] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (atendimento_id, servico_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[historico_atendimentos](
    [id] [bigint] NOT NULL,
    [unidade_id] [int] NULL,
    [usuario_id] [int] NULL,
    [usuario_tri_id] [int] NOT NULL,
    [servico_id] [int] NOT NULL,
    [prioridade_id] [int] NOT NULL,
    [status] [int] NOT NULL,
    [sigla_senha] [char](1) NOT NULL,
    [num_senha] [int] NOT NULL,
    [num_senha_serv] [int] NOT NULL,
    [nm_cli] [varchar](100) NULL,
    [num_guiche] [smallint] NOT NULL,
    [dt_cheg] [datetime2](6) NOT NULL,
    [dt_cha] [datetime2](6) NULL,
    [dt_ini] [datetime2](6) NULL,
    [dt_fim] [datetime2](6) NULL,
    [ident_cli] [varchar](11) NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[modulos](
    [id] [int] identity(1,1) NOT NULL,
    [chave] [varchar](50) NOT NULL,
    [nome] [varchar](25) NOT NULL,
    [descricao] [varchar](100) NOT NULL,
    [autor] [varchar](25) NOT NULL,
    [tipo] [smallint] NOT NULL,
    [status] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[paineis](
    [unidade_id] [int] NOT NULL,
    [host] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (host)
) ON [PRIMARY]

CREATE TABLE [dbo].[paineis_servicos](
    [host] [int] NOT NULL,
    [unidade_id] [int] NOT NULL,
    [servico_id] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (host, servico_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[painel_senha](
    [id] [int] identity(1,1) NOT NULL,
    [unidade_id] [int] NOT NULL,
    [servico_id] [int] NOT NULL,
    [num_senha] [int] NOT NULL,
    [sig_senha] [char](1) NOT NULL,
    [msg_senha] [varchar](20) NOT NULL,
    [local] [varchar](15) NOT NULL,
    [num_guiche] [smallint] NOT NULL,
    [peso] [int] NOT NULL
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[prioridades](
    [id] [int] identity(1,1) NOT NULL,
    [nome] [varchar](30) NOT NULL,
    [descricao] [varchar](100) NOT NULL,
    [peso] [smallint] NOT NULL,
    [status] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[locais](
    [id] [int] identity(1,1) NOT NULL,
    [nome] [varchar](20) NOT NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[serv_peso](
    [servico_id] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (servico_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[servicos](
    [id] [int] identity(1,1) NOT NULL,
    [id_macro] [int] NULL,
    [descricao] [varchar](100) NOT NULL,
    [nome] [varchar](50) NULL,
    [status] [smallint] NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

CREATE TABLE [dbo].[uni_serv](
    [unidade_id] [int] NOT NULL,
    [servico_id] [int] NOT NULL,
    [local_id] [int] NOT NULL,
    [nome] [varchar](50) NOT NULL,
    [sigla] [char](1) NOT NULL,
    [status] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (unidade_id, servico_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[unidades](
    [id] [int] identity(1,1) NOT NULL,
    [grupo_id] [int] NOT NULL,
    [codigo] [varchar](10) NOT NULL,
    [nome] [varchar](50) NULL,
    [status] [smallint] NULL,
    [stat_imp] [smallint] NULL,
    [msg_imp] [varchar](100) NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]


CREATE TABLE [dbo].[usu_grup_cargo](
    [usuario_id] [int] NOT NULL,
    [grupo_id] [int] NOT NULL,
    [cargo_id] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (usuario_id, grupo_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[usu_serv](
    [unidade_id] [int] NOT NULL,
    [servico_id] [int] NOT NULL,
    [usuario_id] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (unidade_id, servico_id, usuario_id)
) ON [PRIMARY]

CREATE TABLE [dbo].[usuarios](
    [id] [int] identity(1,1) NOT NULL,
    [login] [varchar](20) NOT NULL,
    [nome] [varchar](20) NOT NULL,
    [sobrenome] [varchar](100) NOT NULL,
    [senha] [varchar](40) NOT NULL,
    [ult_acesso] [datetime2](6) NULL,
    [status] [smallint] NOT NULL,
    [session_id] [varchar](40) NOT NULL,
    PRIMARY KEY CLUSTERED (id)
) ON [PRIMARY]

-------------
CREATE UNIQUE INDEX IX_codigo ON unidades(codigo)
CREATE INDEX IX_direita ON grupos(direita)
CREATE INDEX IX_esqdir ON grupos(esquerda, direita)
CREATE INDEX IX_esquerda ON grupos(esquerda)
CREATE INDEX IX_fki_atend_codif_ibfk_2 ON atend_codif(servico_id)
CREATE INDEX IX_fki_atendimentos_ibfk_1 ON atendimentos(prioridade_id)
CREATE INDEX IX_fki_atendimentos_ibfk_2 ON atendimentos(unidade_id, servico_id)
CREATE INDEX IX_fki_atendimentos_ibfk_3 ON atendimentos(status)
CREATE INDEX IX_fki_atendimentos_ibfk_4 ON atendimentos(usuario_id)
CREATE INDEX IX_fki_grupo_id ON unidades(grupo_id)
CREATE INDEX IX_fki_servicos_ibfk_1 ON servicos(id_macro)
CREATE INDEX IX_fki_uni_serv_ibfk_2 ON uni_serv(servico_id)
CREATE INDEX IX_fki_uni_serv_ibfk_3 ON uni_serv(local_id)
CREATE INDEX IX_fki_usu_serv_ibfk_1 ON usu_serv(servico_id, unidade_id)
CREATE INDEX IX_fki_usu_serv_ibfk_2 ON usu_serv(usuario_id)
CREATE UNIQUE INDEX IX_local_serv_nm ON locais(nome)
CREATE UNIQUE INDEX IX_login ON usuarios(login)
CREATE UNIQUE INDEX IX_modulos_chave ON modulos(chave)

-- FOREIGN KEY

ALTER TABLE atend_codif ADD CONSTRAINT FK_atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES atendimentos(id)
ALTER TABLE atend_codif ADD CONSTRAINT FK_atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id)
ALTER TABLE atendimentos ADD CONSTRAINT FK_atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id)
ALTER TABLE atendimentos ADD CONSTRAINT FK_atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id)
ALTER TABLE atendimentos ADD CONSTRAINT FK_atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
ALTER TABLE atendimentos ADD CONSTRAINT FK_atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id)
ALTER TABLE cargos_mod_perm ADD CONSTRAINT FK_cargos_mod_perm_ibfk_1 FOREIGN KEY (cargo_id) REFERENCES cargos(id)
ALTER TABLE cargos_mod_perm ADD CONSTRAINT FK_cargos_mod_perm_ibfk_2 FOREIGN KEY (modulo_id) REFERENCES modulos(id)
ALTER TABLE historico_atend_codif ADD CONSTRAINT FK_historico_atend_codif_ibfk_1 FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos(id)
ALTER TABLE historico_atend_codif ADD CONSTRAINT FK_historico_atend_codif_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id)
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_historico_atendimentos_ibfk_1 FOREIGN KEY (prioridade_id) REFERENCES prioridades(id)
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_historico_atendimentos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id)
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_historico_atendimentos_ibfk_4 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
ALTER TABLE historico_atendimentos ADD CONSTRAINT FK_historico_atendimentos_ibfk_5 FOREIGN KEY (usuario_tri_id) REFERENCES usuarios(id)
ALTER TABLE paineis ADD CONSTRAINT FK_paineis_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id)
ALTER TABLE paineis_servicos ADD CONSTRAINT FK_paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host)
ALTER TABLE paineis_servicos ADD CONSTRAINT FK_paineis_servicos_ibfk_2 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv (unidade_id, servico_id)
ALTER TABLE painel_senha ADD CONSTRAINT FK_painel_senha_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id)
ALTER TABLE painel_senha ADD CONSTRAINT FK_painel_senha_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id)
ALTER TABLE serv_peso ADD CONSTRAINT FK_peso_ibfk_1 FOREIGN KEY (servico_id) REFERENCES servicos(id)
ALTER TABLE servicos ADD CONSTRAINT FK_servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id)
ALTER TABLE uni_serv ADD CONSTRAINT FK_uni_serv_ibfk_1 FOREIGN KEY (unidade_id) REFERENCES unidades(id)
ALTER TABLE uni_serv ADD CONSTRAINT FK_uni_serv_ibfk_2 FOREIGN KEY (servico_id) REFERENCES servicos(id)
ALTER TABLE uni_serv ADD CONSTRAINT FK_uni_serv_ibfk_3 FOREIGN KEY (local_id) REFERENCES locais(id)
ALTER TABLE unidades ADD CONSTRAINT FK_unidades_grupo_id_fkey FOREIGN KEY (grupo_id) REFERENCES grupos(id)
ALTER TABLE usu_grup_cargo ADD CONSTRAINT FK_usu_grup_cargo_ibfk_1 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
ALTER TABLE usu_grup_cargo ADD CONSTRAINT FK_usu_grup_cargo_ibfk_2 FOREIGN KEY (grupo_id) REFERENCES grupos(id)
ALTER TABLE usu_grup_cargo ADD CONSTRAINT FK_usu_grup_cargo_ibfk_3 FOREIGN KEY (cargo_id) REFERENCES cargos(id)
ALTER TABLE usu_serv ADD CONSTRAINT FK_usu_serv_ibfk_1 FOREIGN KEY (unidade_id, servico_id) REFERENCES uni_serv(unidade_id, servico_id)
ALTER TABLE usu_serv ADD CONSTRAINT FK_usu_serv_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)


-- VIEWS

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
        historico_atend_codif

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
        historico_atendimentos
