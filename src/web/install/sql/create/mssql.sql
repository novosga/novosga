-- @adapter=MS SQL
-- @author=Sergio Araujo Sielemann
-- @date=2012-10-09

/* Object:  Table [dbo].[atend_codif]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[atend_codif](
    [id_atend] [bigint] NOT NULL,
    [id_serv] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[atend_status]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[atend_status](
    [id_stat] [int] identity(1,1) NOT NULL,
    [nm_stat] [varchar](30) NOT NULL,
    [desc_stat] [varchar](150) NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[atendimentos]    Script Date: 10/08/2012 17:01:28 */
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
    [dt_cheg] [datetime2] NOT NULL,
    [dt_cha] [datetime2] NULL,
    [dt_ini] [datetime2] NULL,
    [dt_fim] [datetime2] NULL,
    [ident_cli] [varchar](11) NULL
) ON [PRIMARY]

GO
/* Object:  Table [dbo].[cargos_aninhados]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[cargos_aninhados](
    [id_cargo] [int] identity(1,1) NOT NULL,
    [nm_cargo] [varchar](30) NOT NULL,
    [desc_cargo] [varchar](140) NULL,
    [esquerda] [int] NOT NULL,
    [direita] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[cargos_mod_perm]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[cargos_mod_perm](
    [id_cargo] [int] NOT NULL,
    [id_mod] [int] NOT NULL,
    [permissao] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[grupos_aninhados]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[grupos_aninhados](
    [id_grupo] [int] identity(1,1) NOT NULL,
    [nm_grupo] [varchar](40) NOT NULL,
    [desc_grupo] [varchar](150) NOT NULL,
    [esquerda] [int] NOT NULL,
    [direita] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[config] */
CREATE TABLE [dbo].[config](
    [chave] [varchar](150) NOT NULL,
    [valor] [text] NOT NULL,
        [tipo] [int] NOT NULL
) ON [PRIMARY]

GO


/* Object:  Table [dbo].[historico_atend_codif]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[historico_atend_codif](
    [id_atend] [bigint] NOT NULL,
    [id_serv] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[historico_atendimentos]    Script Date: 10/08/2012 17:01:28 */
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
    [dt_cheg] [datetime2] NOT NULL,
    [dt_cha] [datetime2] NULL,
    [dt_ini] [datetime2] NULL,
    [dt_fim] [datetime2] NULL,
    [ident_cli] [varchar](11) NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[modulos]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[modulos](
    [id_mod] [int] identity(1,1) NOT NULL,
    [chave_mod] [varchar](50) NOT NULL,
    [nm_mod] [varchar](25) NOT NULL,
    [desc_mod] [varchar](100) NOT NULL,
    [autor_mod] [varchar](25) NOT NULL,
    [tipo_mod] [smallint] NOT NULL,
    [stat_mod] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[paineis]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[paineis](
    [id_uni] [int] NOT NULL,
    [host] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[paineis_servicos]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[paineis_servicos](
    [host] [int] NOT NULL,
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[painel_senha]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[painel_senha](
    [contador] [int] identity(1,1) NOT NULL,
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [num_senha] [int] NOT NULL,
    [sig_senha] [char](1) NOT NULL,
    [msg_senha] [varchar](20) NOT NULL,
    [nm_local] [varchar](15) NOT NULL,
    [num_guiche] [smallint] NOT NULL,
    [dt_envio] [datetime2] NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[prioridades]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[prioridades](
    [id_pri] [int] identity(1,1) NOT NULL,
    [nm_pri] [varchar](30) NOT NULL,
    [desc_pri] [varchar](100) NOT NULL,
    [peso_pri] [smallint] NOT NULL,
    [stat_pri] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[serv_local]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[serv_local](
    [id_loc] [int] identity(1,1) NOT NULL,
    [nm_loc] [varchar](20) NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[serv_peso]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[serv_peso](
    [id_serv] [int] NOT NULL,
    [valor_peso] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[servicos]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[servicos](
    [id_serv] [int] identity(1,1) NOT NULL,
    [id_macro] [int] NULL,
    [desc_serv] [varchar](100) NOT NULL,
    [nm_serv] [varchar](50) NULL,
    [stat_serv] [smallint] NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[uni_serv]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[uni_serv](
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [id_loc] [int] NOT NULL,
    [nm_serv] [varchar](50) NOT NULL,
    [sigla_serv] [char](1) NOT NULL,
    [stat_serv] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[unidades]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[unidades](
    [id_uni] [int] identity(1,1) NOT NULL,
    [id_grupo] [int] NOT NULL,
    [cod_uni] [varchar](10) NOT NULL,
    [nm_uni] [varchar](50) NULL,
    [stat_uni] [smallint] NULL,
    [stat_imp] [smallint] NULL,
        [msg_imp] [varchar](100) NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[usu_grup_cargo]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[usu_grup_cargo](
    [id_usu] [int] NOT NULL,
    [id_grupo] [int] NOT NULL,
    [id_cargo] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[usu_serv]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[usu_serv](
    [id_uni] [int] NOT NULL,
    [id_serv] [int] NOT NULL,
    [id_usu] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[usuarios]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[usuarios](
    [id_usu] [int] identity(1,1) NOT NULL,
    [login_usu] [varchar](20) NOT NULL,
    [nm_usu] [varchar](20) NOT NULL,
    [ult_nm_usu] [varchar](100) NOT NULL,
    [senha_usu] [varchar](40) NOT NULL,
    [ult_acesso] [datetime2] NULL,
    [stat_usu] [smallint] NOT NULL,
    [session_id] [varchar](40) NOT NULL
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[senha_uni_msg] ADD  DEFAULT ((0)) FOR [status_imp]
GO

ALTER TABLE [dbo].[unidades] ADD  CONSTRAINT [DEFAULT_STAT_UNI]  DEFAULT ((1)) FOR [stat_uni]
GO


/* PRIMARY KEYS */
------------------
ALTER TABLE atend_codif
    WITH NOCHECK ADD CONSTRAINT PK_atend_codif_pkey PRIMARY KEY CLUSTERED (id_atend, id_serv)
GO

ALTER TABLE atend_status
    WITH NOCHECK ADD CONSTRAINT PK_atend_status_pkey PRIMARY KEY CLUSTERED (id_stat)
go

ALTER TABLE atendimentos
    WITH NOCHECK ADD CONSTRAINT PK_atendimentos_pkey PRIMARY KEY CLUSTERED (id_atend)
go

ALTER TABLE cargos_aninhados
    WITH NOCHECK ADD CONSTRAINT PK_cargos_aninhados_pkey PRIMARY KEY CLUSTERED (id_cargo)
go

ALTER TABLE cargos_mod_perm
    WITH NOCHECK ADD CONSTRAINT PK_cargos_mod_perm_pkey PRIMARY KEY CLUSTERED (id_cargo, id_mod)
go

ALTER TABLE grupos_aninhados
    WITH NOCHECK ADD CONSTRAINT PK_grupos_aninhados_pkey PRIMARY KEY CLUSTERED (id_grupo)
go

ALTER TABLE config
    WITH NOCHECK ADD CONSTRAINT config_pkey PRIMARY KEY CLUSTERED (chave)
go

ALTER TABLE historico_atend_codif
    WITH NOCHECK ADD CONSTRAINT PK_historico_atend_codif_pkey PRIMARY KEY CLUSTERED (id_atend, id_serv)
go

ALTER TABLE historico_atendimentos
    WITH NOCHECK ADD CONSTRAINT PK_historico_atendimentos_pkey PRIMARY KEY CLUSTERED (id_atend)
go

ALTER TABLE modulos
    WITH NOCHECK ADD CONSTRAINT PK_modulos_pkey PRIMARY KEY CLUSTERED (id_mod)
go

ALTER TABLE paineis
    WITH NOCHECK ADD CONSTRAINT PK_paineis_pkey PRIMARY KEY CLUSTERED (host)
go

ALTER TABLE paineis_servicos
    WITH NOCHECK ADD CONSTRAINT PK_paineis_servicos_pkey PRIMARY KEY CLUSTERED (host, id_serv)
go

ALTER TABLE painel_senha
    WITH NOCHECK ADD CONSTRAINT PK_painel_senha_pkey PRIMARY KEY CLUSTERED (contador)
go

ALTER TABLE prioridades
    WITH NOCHECK ADD CONSTRAINT PK_prioridades_pkey PRIMARY KEY CLUSTERED (id_pri)
go

ALTER TABLE senha_uni_msg
    WITH NOCHECK ADD CONSTRAINT PK_senha_uni_msg_pkey PRIMARY KEY CLUSTERED (id_uni)
go

ALTER TABLE serv_local
    WITH NOCHECK ADD CONSTRAINT PK_serv_local_pkey PRIMARY KEY CLUSTERED (id_loc)
go

ALTER TABLE serv_peso
    WITH NOCHECK ADD CONSTRAINT PK_serv_peso_pkey PRIMARY KEY CLUSTERED (id_serv)
go

ALTER TABLE servicos
    WITH NOCHECK ADD CONSTRAINT PK_servicos_pkey PRIMARY KEY CLUSTERED (id_serv)
go

ALTER TABLE uni_serv
    WITH NOCHECK ADD CONSTRAINT PK_uni_serv_pkey PRIMARY KEY CLUSTERED (id_uni, id_serv)
go

ALTER TABLE unidades
    WITH NOCHECK ADD CONSTRAINT PK_unidades_pkey PRIMARY KEY CLUSTERED (id_uni)
go

ALTER TABLE usu_grup_cargo
    WITH NOCHECK ADD CONSTRAINT PK_usu_grup_cargo_pkey PRIMARY KEY CLUSTERED (id_usu, id_grupo)
go

ALTER TABLE usu_serv
    WITH NOCHECK ADD CONSTRAINT PK_usu_serv_pkey PRIMARY KEY CLUSTERED (id_uni, id_serv, id_usu)
go

ALTER TABLE usuarios
    WITH NOCHECK ADD CONSTRAINT PK_usuarios_pkey PRIMARY KEY CLUSTERED (id_usu)
go

/* INDICES */
-------------
CREATE UNIQUE INDEX IX_cod_uni ON unidades(cod_uni)
go

CREATE INDEX IX_direita ON grupos_aninhados(direita)
go

CREATE INDEX IX_esqdir ON grupos_aninhados(esquerda, direita)
go

CREATE INDEX IX_esquerda ON grupos_aninhados(esquerda)
go

CREATE INDEX IX_fki_atend_codif_ibfk_2 ON atend_codif(id_serv)
go

CREATE INDEX IX_fki_atendimentos_ibfk_1 ON atendimentos(id_pri)
go

CREATE INDEX IX_fki_atendimentos_ibfk_2 ON atendimentos(id_uni, id_serv)
go

CREATE INDEX IX_fki_atendimentos_ibfk_3 ON atendimentos(id_stat)
go

CREATE INDEX IX_fki_atendimentos_ibfk_4 ON atendimentos(id_usu)
go

CREATE INDEX IX_fki_id_grupo ON unidades(id_grupo)
go

CREATE INDEX IX_fki_servicos_ibfk_1 ON servicos(id_macro)
go

CREATE INDEX IX_fki_uni_serv_ibfk_2 ON uni_serv(id_serv)
go

CREATE INDEX IX_fki_uni_serv_ibfk_3 ON uni_serv(id_loc)
go

CREATE INDEX IX_fki_usu_serv_ibfk_1 ON usu_serv(id_serv, id_uni)
go

CREATE INDEX IX_fki_usu_serv_ibfk_2 ON usu_serv(id_usu)
go

CREATE UNIQUE INDEX IX_local_serv_nm ON serv_local(nm_loc)
go

CREATE UNIQUE INDEX IX_login_usu ON usuarios(login_usu)
go

CREATE UNIQUE INDEX IX_modulos_chave ON modulos(chave_mod)
go

/* FOREIGN KEY */
-----------------
ALTER TABLE atend_codif
    ADD CONSTRAINT FK_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend)
go

ALTER TABLE atend_codif
    ADD CONSTRAINT FK_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri)
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv)
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat)
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_5 FOREIGN KEY (id_usu_tri) REFERENCES usuarios(id_usu)
go

ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT FK_cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo)
go

ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT FK_cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod)
go

ALTER TABLE historico_atend_codif
    ADD CONSTRAINT FK_historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend)
go

ALTER TABLE historico_atend_codif
    ADD CONSTRAINT FK_historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri)
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv)
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat)
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_5 FOREIGN KEY (id_usu_tri) REFERENCES usuarios(id_usu)
go

ALTER TABLE paineis
    ADD CONSTRAINT FK_paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
go

ALTER TABLE paineis_servicos
    ADD CONSTRAINT FK_paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host)
go

ALTER TABLE paineis_servicos
    ADD CONSTRAINT FK_paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv)
go

ALTER TABLE painel_senha
    ADD CONSTRAINT FK_painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
go

ALTER TABLE painel_senha
    ADD CONSTRAINT FK_painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
go

ALTER TABLE serv_peso
    ADD CONSTRAINT FK_peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
go

ALTER TABLE senha_uni_msg
    ADD CONSTRAINT FK_senha_uni_msg_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
go

ALTER TABLE servicos
    ADD CONSTRAINT FK_servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv)
go

ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni)
go

ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv)
go

ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc)
go

ALTER TABLE unidades
    ADD CONSTRAINT FK_unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo)
go

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
go

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo)
go

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo)
go

ALTER TABLE usu_serv
    ADD CONSTRAINT FK_usu_serv_ibfk_1 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv)
go

ALTER TABLE usu_serv
    ADD CONSTRAINT FK_usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu)
go

/* VIEWS */
-----------
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
go

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
go
