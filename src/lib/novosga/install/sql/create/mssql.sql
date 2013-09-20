-- @adapter=MS SQL
-- @author=rogeriolino
-- @date=2013-08-29

CREATE TABLE [dbo].[atend_codif](
    [id_atend] [bigint] NOT NULL,
    [id_serv] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id_atend, id_serv)
) ON [PRIMARY]

CREATE TABLE [dbo].[atend_status](
    [id_stat] [int] identity(1,1) NOT NULL,
    [nm_stat] [varchar](30) NOT NULL,
    [desc_stat] [varchar](150) NOT NULL,
    PRIMARY KEY CLUSTERED (id_stat)
) ON [PRIMARY]

CREATE TABLE [dbo].[atendimentos](
    [id_atend] [bigint] identity(1,1) NOT NULL,
    [id_uni] [int] NULL,
    [id_usu] [int] NULL,
    [id_usu_tri] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [id_pri] [int] NOT NULL,
    [id_stat] [int] NOT NULL,
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
    PRIMARY KEY CLUSTERED (id_atend)
) ON [PRIMARY]

CREATE TABLE [dbo].[cargos_aninhados](
    [id_cargo] [int] identity(1,1) NOT NULL,
    [nm_cargo] [varchar](30) NOT NULL,
    [desc_cargo] [varchar](140) NULL,
    [esquerda] [int] NOT NULL,
    [direita] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id_cargo)
) ON [PRIMARY]

CREATE TABLE [dbo].[cargos_mod_perm](
    [id_cargo] [int] NOT NULL,
    [id_mod] [int] NOT NULL,
    [permissao] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id_cargo, id_mod)
) ON [PRIMARY]

CREATE TABLE [dbo].[grupos_aninhados](
    [id_grupo] [int] identity(1,1) NOT NULL,
    [nm_grupo] [varchar](40) NOT NULL,
    [desc_grupo] [varchar](150) NOT NULL,
    [esquerda] [int] NOT NULL,
    [direita] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id_grupo)
) ON [PRIMARY]

CREATE TABLE [dbo].[config](
    [chave] [varchar](150) NOT NULL,
    [valor] [text] NOT NULL,
    [tipo] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (chave)
) ON [PRIMARY]


CREATE TABLE [dbo].[historico_atend_codif](
    [id_atend] [bigint] NOT NULL,
    [id_serv] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id_atend, id_serv)
) ON [PRIMARY]

CREATE TABLE [dbo].[historico_atendimentos](
    [id_atend] [bigint] NOT NULL,
    [id_uni] [int] NULL,
    [id_usu] [int] NULL,
    [id_usu_tri] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [id_pri] [int] NOT NULL,
    [id_stat] [int] NOT NULL,
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
    PRIMARY KEY CLUSTERED (id_atend)
) ON [PRIMARY]

CREATE TABLE [dbo].[modulos](
    [id_mod] [int] identity(1,1) NOT NULL,
    [chave_mod] [varchar](50) NOT NULL,
    [nm_mod] [varchar](25) NOT NULL,
    [desc_mod] [varchar](100) NOT NULL,
    [autor_mod] [varchar](25) NOT NULL,
    [tipo_mod] [smallint] NOT NULL,
    [stat_mod] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id_mod)
) ON [PRIMARY]

CREATE TABLE [dbo].[paineis](
    [id_uni] [int] NOT NULL,
    [host] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (host)
) ON [PRIMARY]

CREATE TABLE [dbo].[paineis_servicos](
    [host] [int] NOT NULL,
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (host, id_serv)
) ON [PRIMARY]

CREATE TABLE [dbo].[painel_senha](
    [contador] [int] identity(1,1) NOT NULL,
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [num_senha] [int] NOT NULL,
    [sig_senha] [char](1) NOT NULL,
    [msg_senha] [varchar](20) NOT NULL,
    [nm_local] [varchar](15) NOT NULL,
    [num_guiche] [smallint] NOT NULL,
    [dt_envio] [datetime2](6) NULL,
    PRIMARY KEY CLUSTERED (contador)
) ON [PRIMARY]

CREATE TABLE [dbo].[prioridades](
    [id_pri] [int] identity(1,1) NOT NULL,
    [nm_pri] [varchar](30) NOT NULL,
    [desc_pri] [varchar](100) NOT NULL,
    [peso_pri] [smallint] NOT NULL,
    [stat_pri] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id_pri)
) ON [PRIMARY]

CREATE TABLE [dbo].[serv_local](
    [id_loc] [int] identity(1,1) NOT NULL,
    [nm_loc] [varchar](20) NOT NULL,
    PRIMARY KEY CLUSTERED (id_loc)
) ON [PRIMARY]

CREATE TABLE [dbo].[serv_peso](
    [id_serv] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id_serv)
) ON [PRIMARY]

CREATE TABLE [dbo].[servicos](
    [id_serv] [int] identity(1,1) NOT NULL,
    [id_macro] [int] NULL,
    [desc_serv] [varchar](100) NOT NULL,
    [nm_serv] [varchar](50) NULL,
    [stat_serv] [smallint] NULL,
    PRIMARY KEY CLUSTERED (id_serv)
) ON [PRIMARY]

CREATE TABLE [dbo].[uni_serv](
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [id_loc] [int] NOT NULL,
    [nm_serv] [varchar](50) NOT NULL,
    [sigla_serv] [char](1) NOT NULL,
    [stat_serv] [smallint] NOT NULL,
    PRIMARY KEY CLUSTERED (id_uni, id_serv)
) ON [PRIMARY]

CREATE TABLE [dbo].[unidades](
    [id_uni] [int] identity(1,1) NOT NULL,
    [id_grupo] [int] NOT NULL,
    [cod_uni] [varchar](10) NOT NULL,
    [nm_uni] [varchar](50) NULL,
    [stat_uni] [smallint] NULL,
    [stat_imp] [smallint] NULL,
    [msg_imp] [varchar](100) NULL,
    PRIMARY KEY CLUSTERED (id_uni)
) ON [PRIMARY]


CREATE TABLE [dbo].[usu_grup_cargo](
    [id_usu] [int] NOT NULL,
    [id_grupo] [int] NOT NULL,
    [id_cargo] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id_usu, id_grupo)
) ON [PRIMARY]

CREATE TABLE [dbo].[usu_serv](
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [id_usu] [int] NOT NULL,
    PRIMARY KEY CLUSTERED (id_uni, id_serv, id_usu)
) ON [PRIMARY]

CREATE TABLE [dbo].[usuarios](
    [id_usu] [int] identity(1,1) NOT NULL,
    [login_usu] [varchar](20) NOT NULL,
    [nm_usu] [varchar](20) NOT NULL,
    [ult_nm_usu] [varchar](100) NOT NULL,
    [senha_usu] [varchar](40) NOT NULL,
    [ult_acesso] [datetime2](6) NULL,
    [stat_usu] [smallint] NOT NULL,
    [session_id] [varchar](40) NOT NULL,
    PRIMARY KEY CLUSTERED (id_usu)
) ON [PRIMARY]

-------------
CREATE UNIQUE INDEX IX_cod_uni ON unidades(cod_uni)
CREATE INDEX IX_direita ON grupos_aninhados(direita)
CREATE INDEX IX_esqdir ON grupos_aninhados(esquerda, direita)
CREATE INDEX IX_esquerda ON grupos_aninhados(esquerda)
CREATE INDEX IX_fki_atend_codif_ibfk_2 ON atend_codif(id_serv)
CREATE INDEX IX_fki_atendimentos_ibfk_1 ON atendimentos(id_pri)
CREATE INDEX IX_fki_atendimentos_ibfk_2 ON atendimentos(id_uni, id_serv)
CREATE INDEX IX_fki_atendimentos_ibfk_3 ON atendimentos(id_stat)
CREATE INDEX IX_fki_atendimentos_ibfk_4 ON atendimentos(id_usu)
CREATE INDEX IX_fki_id_grupo ON unidades(id_grupo)
CREATE INDEX IX_fki_servicos_ibfk_1 ON servicos(id_macro)
CREATE INDEX IX_fki_uni_serv_ibfk_2 ON uni_serv(id_serv)
CREATE INDEX IX_fki_uni_serv_ibfk_3 ON uni_serv(id_loc)
CREATE INDEX IX_fki_usu_serv_ibfk_1 ON usu_serv(id_serv, id_uni)
CREATE INDEX IX_fki_usu_serv_ibfk_2 ON usu_serv(id_usu)
CREATE UNIQUE INDEX IX_local_serv_nm ON serv_local(nm_loc)
CREATE UNIQUE INDEX IX_login_usu ON usuarios(login_usu)
CREATE UNIQUE INDEX IX_modulos_chave ON modulos(chave_mod)

-- FOREIGN KEY

ALTER TABLE atend_codif 
    ADD CONSTRAINT FK_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend)
ALTER TABLE atend_codif
    ADD CONSTRAINT FK_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri)
ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv)
ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat)
ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_5 FOREIGN KEY (id_usu_tri) REFERENCES usuarios(id_usu)
ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT FK_cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo)
ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT FK_cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod)
ALTER TABLE historico_atend_codif
    ADD CONSTRAINT FK_historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend)
ALTER TABLE historico_atend_codif
    ADD CONSTRAINT FK_historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri)
ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv)
ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat)
ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_5 FOREIGN KEY (id_usu_tri) REFERENCES usuarios(id_usu)
ALTER TABLE paineis
    ADD CONSTRAINT FK_paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
ALTER TABLE paineis_servicos
    ADD CONSTRAINT FK_paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host)
ALTER TABLE paineis_servicos
    ADD CONSTRAINT FK_paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv)
ALTER TABLE painel_senha
    ADD CONSTRAINT FK_painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
ALTER TABLE painel_senha
    ADD CONSTRAINT FK_painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
ALTER TABLE serv_peso
    ADD CONSTRAINT FK_peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
ALTER TABLE servicos
    ADD CONSTRAINT FK_servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv)
ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc)
ALTER TABLE unidades
    ADD CONSTRAINT FK_unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo)
ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo)
ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo)
ALTER TABLE usu_serv
    ADD CONSTRAINT FK_usu_serv_ibfk_1 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv)
ALTER TABLE usu_serv
    ADD CONSTRAINT FK_usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)


-- VIEWS

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
        historico_atend_codif

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
        historico_atendimentos.id_atend, 
        historico_atendimentos.id_uni, 
        historico_atendimentos.id_usu, 
        historico_atendimentos.id_usu_tri, 
        historico_atendimentos.id_serv, 
        historico_atendimentos.id_pri, 
        historico_atendimentos.id_stat, 
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
