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
	[id_serv] [int] NOT NULL,
	[id_pri] [int] NOT NULL,
	[id_stat] [int] NOT NULL,
	[num_senha] [int] NOT NULL,
	[nm_cli] [varchar](100) NULL,
	[num_guiche] [smallint] NOT NULL,
	[dt_cheg] [datetime] NOT NULL,
	[dt_cha] [datetime] NULL,
	[dt_ini] [datetime] NULL,
	[dt_fim] [datetime] NULL,
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
	[id_serv] [int] NOT NULL,
	[id_pri] [int] NOT NULL,
	[id_stat] [int] NOT NULL,
	[num_senha] [int] NOT NULL,
	[nm_cli] [varchar](100) NULL,
	[num_guiche] [smallint] NOT NULL,
	[dt_cheg] [datetime] NOT NULL,
	[dt_cha] [datetime] NULL,
	[dt_ini] [datetime] NULL,
	[dt_fim] [datetime] NULL,
	[ident_cli] [varchar](11) NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[menus]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[menus](
	[id_menu] [int] identity(1,1) NOT NULL,
	[id_mod] [int] NOT NULL,
	[nm_menu] [varchar](50) NOT NULL,
	[desc_menu] [varchar](100) NOT NULL,
	[lnk_menu] [varchar](150) NOT NULL,
	[ord_menu] [smallint] NOT NULL,
	[id_mod_impl] [int] NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[modulos]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[modulos](
	[id_mod] [int] identity(1,1) NOT NULL,
	[chave_mod] [varchar](50) NOT NULL,
	[nm_mod] [varchar](25) NOT NULL,
	[desc_mod] [varchar](100) NOT NULL,
	[autor_mod] [varchar](25) NOT NULL,
	[img_mod] [varchar](150) NULL,
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
	[msg_senha] [varchar](15) NOT NULL,
	[nm_local] [varchar](15) NOT NULL,
	[num_guiche] [smallint] NOT NULL
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

/* Object:  Table [dbo].[senha_uni_msg]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[senha_uni_msg](
	[id_uni] [int] NOT NULL,
	[id_usu] [int] NOT NULL,
	[msg_local] [varchar](100) NULL,
	[msg_global] [varchar](100) NULL,
	[status_imp] [int] NULL
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

/* Object:  Table [dbo].[temas]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[temas](
	[id_tema] [int] identity(1,1) NOT NULL,
	[nm_tema] [varchar](25) NOT NULL,
	[desc_tema] [varchar](100) NOT NULL,
	[autor_tema] [varchar](25) NOT NULL,
	[dir_tema] [varchar](50) NOT NULL
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
	[id_tema] [int] NOT NULL,
	[cod_uni] [varchar](10) NOT NULL,
	[nm_uni] [varchar](50) NULL,
	[stat_uni] [smallint] NULL
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

/* Object:  Table [dbo].[usu_session]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[usu_session](
	[id_usu] [int] NOT NULL,
	[session_id] [varchar](40) NOT NULL,
	[stat_session] [int] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[usuarios]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[usuarios](
	[id_usu] [int] identity(1,1) NOT NULL,
	[login_usu] [varchar](20) NOT NULL,
	[nm_usu] [varchar](20) NOT NULL,
	[ult_nm_usu] [varchar](100) NOT NULL,
	[senha_usu] [varchar](40) NOT NULL,
	[ult_acesso] [datetime] NULL,
	[stat_usu] [smallint] NOT NULL
) ON [PRIMARY]

GO

/* Object:  Table [dbo].[variaveis_sistema]    Script Date: 10/08/2012 17:01:28 */
CREATE TABLE [dbo].[variaveis_sistema](
	[chave] [varchar](20) NOT NULL,
	[valor] [varchar](128) NOT NULL
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[senha_uni_msg] ADD  DEFAULT ((0)) FOR [status_imp]
GO

ALTER TABLE [dbo].[unidades] ADD  CONSTRAINT [DEFAULT_STAT_UNI]  DEFAULT ((1)) FOR [stat_uni]
GO


/* PRIMARY KEYS */
------------------
ALTER TABLE atend_codif
    WITH NOCHECK ADD CONSTRAINT PK_atend_codif_pkey PRIMARY KEY CLUSTERED (id_atend, id_serv);
GO

ALTER TABLE atend_status
    WITH NOCHECK ADD CONSTRAINT PK_atend_status_pkey PRIMARY KEY CLUSTERED (id_stat);
go

ALTER TABLE atendimentos
    WITH NOCHECK ADD CONSTRAINT PK_atendimentos_pkey PRIMARY KEY CLUSTERED (id_atend);
go

ALTER TABLE cargos_aninhados
    WITH NOCHECK ADD CONSTRAINT PK_cargos_aninhados_pkey PRIMARY KEY CLUSTERED (id_cargo);
go

ALTER TABLE cargos_mod_perm
    WITH NOCHECK ADD CONSTRAINT PK_cargos_mod_perm_pkey PRIMARY KEY CLUSTERED (id_cargo, id_mod);
go

ALTER TABLE temas
    WITH NOCHECK ADD CONSTRAINT PK_dir_temas UNIQUE (dir_tema);
go

ALTER TABLE grupos_aninhados
    WITH NOCHECK ADD CONSTRAINT PK_grupos_aninhados_pkey PRIMARY KEY CLUSTERED (id_grupo);
go

ALTER TABLE historico_atend_codif
    WITH NOCHECK ADD CONSTRAINT PK_historico_atend_codif_pkey PRIMARY KEY CLUSTERED (id_atend, id_serv);
go

ALTER TABLE historico_atendimentos
    WITH NOCHECK ADD CONSTRAINT PK_historico_atendimentos_pkey PRIMARY KEY CLUSTERED (id_atend);
go

ALTER TABLE menus
    WITH NOCHECK ADD CONSTRAINT PK_menus_pkey PRIMARY KEY CLUSTERED (id_menu);
go

ALTER TABLE modulos
    WITH NOCHECK ADD CONSTRAINT PK_modulos_pkey PRIMARY KEY CLUSTERED (id_mod);
go

ALTER TABLE paineis
    WITH NOCHECK ADD CONSTRAINT PK_paineis_pkey PRIMARY KEY CLUSTERED (host);
go

ALTER TABLE paineis_servicos
    WITH NOCHECK ADD CONSTRAINT PK_paineis_servicos_pkey PRIMARY KEY CLUSTERED (host, id_serv);
go

ALTER TABLE painel_senha
    WITH NOCHECK ADD CONSTRAINT PK_painel_senha_pkey PRIMARY KEY CLUSTERED (contador);
go

ALTER TABLE prioridades
    WITH NOCHECK ADD CONSTRAINT PK_prioridades_pkey PRIMARY KEY CLUSTERED (id_pri);
go

ALTER TABLE senha_uni_msg
    WITH NOCHECK ADD CONSTRAINT PK_senha_uni_msg_pkey PRIMARY KEY CLUSTERED (id_uni);
go

ALTER TABLE serv_local
    WITH NOCHECK ADD CONSTRAINT PK_serv_local_pkey PRIMARY KEY CLUSTERED (id_loc);
go

ALTER TABLE serv_peso
    WITH NOCHECK ADD CONSTRAINT PK_serv_peso_pkey PRIMARY KEY CLUSTERED (id_serv);
go

ALTER TABLE servicos
    WITH NOCHECK ADD CONSTRAINT PK_servicos_pkey PRIMARY KEY CLUSTERED (id_serv);
go

ALTER TABLE temas
    WITH NOCHECK ADD CONSTRAINT PK_temas_pkey PRIMARY KEY CLUSTERED (id_tema);
go

ALTER TABLE uni_serv
    WITH NOCHECK ADD CONSTRAINT PK_uni_serv_pkey PRIMARY KEY CLUSTERED (id_uni, id_serv);
go

ALTER TABLE unidades
    WITH NOCHECK ADD CONSTRAINT PK_unidades_pkey PRIMARY KEY CLUSTERED (id_uni);
go

ALTER TABLE usu_grup_cargo
    WITH NOCHECK ADD CONSTRAINT PK_usu_grup_cargo_pkey PRIMARY KEY CLUSTERED (id_usu, id_grupo);
go

ALTER TABLE usu_serv
    WITH NOCHECK ADD CONSTRAINT PK_usu_serv_pkey PRIMARY KEY CLUSTERED (id_uni, id_serv, id_usu);
go

ALTER TABLE usu_session
    WITH NOCHECK ADD CONSTRAINT PK_usu_session_pkey PRIMARY KEY CLUSTERED (id_usu);
go

ALTER TABLE usuarios
    WITH NOCHECK ADD CONSTRAINT PK_usuarios_pkey PRIMARY KEY CLUSTERED (id_usu);
go

ALTER TABLE variaveis_sistema
    WITH NOCHECK ADD CONSTRAINT PK_variaveis_sistema_pkey PRIMARY KEY CLUSTERED (chave);
go


/* INDICES */
-------------
CREATE UNIQUE INDEX IX_cod_uni ON unidades(cod_uni);
go

CREATE INDEX IX_direita ON grupos_aninhados(direita);
go

CREATE INDEX IX_esqdir ON grupos_aninhados(esquerda, direita);
go

CREATE INDEX IX_esquerda ON grupos_aninhados(esquerda);
go

CREATE INDEX IX_fki_atend_codif_ibfk_2 ON atend_codif(id_serv);
go

CREATE INDEX IX_fki_atendimentos_ibfk_1 ON atendimentos(id_pri);
go

CREATE INDEX IX_fki_atendimentos_ibfk_2 ON atendimentos(id_uni, id_serv);
go

CREATE INDEX IX_fki_atendimentos_ibfk_3 ON atendimentos(id_stat);
go

CREATE INDEX IX_fki_atendimentos_ibfk_4 ON atendimentos(id_usu);
go

CREATE INDEX IX_fki_id_grupo ON unidades(id_grupo);
go

CREATE INDEX IX_fki_menus_ibfk_1 ON menus(id_mod);
go

CREATE INDEX IX_fki_servicos_ibfk_1 ON servicos(id_macro);
go

CREATE INDEX IX_fki_uni_serv_ibfk_2 ON uni_serv(id_serv);
go

CREATE INDEX IX_fki_uni_serv_ibfk_3 ON uni_serv(id_loc);
go

CREATE INDEX IX_fki_unidades_ibfk_1 ON unidades(id_tema);
go

CREATE INDEX IX_fki_usu_serv_ibfk_1 ON usu_serv(id_serv, id_uni);
go

CREATE INDEX IX_fki_usu_serv_ibfk_2 ON usu_serv(id_usu);
go

CREATE UNIQUE INDEX IX_local_serv_nm ON serv_local(nm_loc);
go

CREATE UNIQUE INDEX IX_login_usu ON usuarios(login_usu);
go

CREATE UNIQUE INDEX IX_modulos_chave ON modulos(chave_mod);
go

/* FOREIGN KEY */
-----------------
ALTER TABLE atend_codif
    ADD CONSTRAINT FK_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend);
go

ALTER TABLE atend_codif
    ADD CONSTRAINT FK_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv);
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri);
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv);
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat);
go

ALTER TABLE atendimentos
    ADD CONSTRAINT FK_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu);
go

ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT FK_cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo);
go

ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT FK_cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod);
go

ALTER TABLE historico_atend_codif
    ADD CONSTRAINT FK_historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend);
go

ALTER TABLE historico_atend_codif
    ADD CONSTRAINT FK_historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv);
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri);
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv);
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat);
go

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT FK_historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu);
go

ALTER TABLE menus
    ADD CONSTRAINT FK_menus_ibfk_1 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod);
go

ALTER TABLE menus
    ADD CONSTRAINT FK_menus_ibfk_2 FOREIGN KEY (id_mod_impl) REFERENCES modulos(id_mod);
go

ALTER TABLE paineis
    ADD CONSTRAINT FK_paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni);
go

ALTER TABLE paineis_servicos
    ADD CONSTRAINT FK_paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host);
go

ALTER TABLE paineis_servicos
    ADD CONSTRAINT FK_paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv);
go

ALTER TABLE painel_senha
    ADD CONSTRAINT FK_painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni);
go

ALTER TABLE painel_senha
    ADD CONSTRAINT FK_painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv);
go

ALTER TABLE serv_peso
    ADD CONSTRAINT FK_peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv);
go

ALTER TABLE senha_uni_msg
    ADD CONSTRAINT FK_senha_uni_msg_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni);
go

ALTER TABLE servicos
    ADD CONSTRAINT FK_servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv);
go

ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni);
go

ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv);
go

ALTER TABLE uni_serv
    ADD CONSTRAINT FK_uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc);
go

ALTER TABLE unidades
    ADD CONSTRAINT FK_unidades_ibfk_1 FOREIGN KEY (id_tema) REFERENCES temas(id_tema);
go

ALTER TABLE unidades
    ADD CONSTRAINT FK_unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo);
go

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu);
go

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo);
go

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT FK_usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo);
go

ALTER TABLE usu_serv
    ADD CONSTRAINT FK_usu_serv_ibfk_1 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv);
go

ALTER TABLE usu_serv
    ADD CONSTRAINT FK_usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu);
go

ALTER TABLE usu_session
    ADD CONSTRAINT FK_usu_session_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu);
go

/* VIEWS */
-----------
CREATE VIEW view_historico_atend_codif 
AS
SELECT atend_codif.id_atend, atend_codif.id_serv, atend_codif.valor_peso 
  FROM atend_codif 
UNION ALL 
SELECT historico_atend_codif.id_atend, historico_atend_codif.id_serv, historico_atend_codif.valor_peso 
  FROM historico_atend_codif;
go

CREATE VIEW view_historico_atendimentos 
AS
SELECT atendimentos.id_atend, atendimentos.id_uni, atendimentos.id_usu, atendimentos.id_serv, atendimentos.id_pri, atendimentos.id_stat, atendimentos.num_senha, atendimentos.nm_cli, atendimentos.num_guiche, atendimentos.dt_cheg, atendimentos.dt_cha, atendimentos.dt_ini, atendimentos.dt_fim, atendimentos.ident_cli FROM atendimentos UNION ALL SELECT historico_atendimentos.id_atend, historico_atendimentos.id_uni, historico_atendimentos.id_usu, historico_atendimentos.id_serv, historico_atendimentos.id_pri, historico_atendimentos.id_stat, historico_atendimentos.num_senha, historico_atendimentos.nm_cli, historico_atendimentos.num_guiche, historico_atendimentos.dt_cheg, historico_atendimentos.dt_cha, historico_atendimentos.dt_ini, historico_atendimentos.dt_fim, historico_atendimentos.ident_cli 
FROM historico_atendimentos;
go

/* POPULA AS TABELAS */
-----------------------
set identity_insert atend_status ON
go
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (1, 'Passou pela Triagem', 'Cliente chegou na unidade e foi atendido pela Triagem');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (3, 'Atendimento Iniciado', 'Cliente chegou no guiche e esta sendo atendido');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (5, 'Nao Compareceu', 'Apos chamar o proximo da fila, o mesmo nao compareceu');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (6, 'Cancelada', 'Senha Cancelada por algum motivo');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (7, 'Erro de triagem', 'Responsavel pela Triagem emitiu a senha para atendimento errado');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (9, 'Agendamento não confirmado', '');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (2, 'Chamado pela mesa', 'Atendente chamou o proximo da fila');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (4, 'Atendimento Encerrado', 'Atendente encerrou o atendimento, mas ainda não codificou os serviços atendidos.');
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (8, 'Atendimento Codificado', 'Atendimento finalizado.');
set identity_insert atend_status OFF
go

set identity_insert cargos_aninhados ON
go
INSERT INTO cargos_aninhados (id_cargo, nm_cargo, desc_cargo, esquerda, direita) VALUES (1, 'Administrador', 'Administrador geral do sistema', 1, 2);
set identity_insert cargos_aninhados OFF
go

set identity_insert cargos_mod_perm ON
go
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 1, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 2, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 3, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 4, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 5, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 6, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 7, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 8, 3);
set identity_insert cargos_mod_perm OFF
go



set identity_insert usuarios ON
go
INSERT INTO usuarios (id_usu, login_usu, nm_usu, ult_nm_usu, senha_usu, ult_acesso, stat_usu)
VALUES (1, '%login_usu%', '%nm_usu%', '%ult_nm_usu%', '%senha_usu%', NULL, 1)
set identity_insert usuarios OFF
go


set identity_insert usu_grup_cargo ON
go
INSERT INTO usu_grup_cargo (id_usu, id_grupo, id_cargo) VALUES (1, 1, 1);
set identity_insert usu_grup_cargo OFF
go


set identity_insert serv_local ON
go
INSERT INTO serv_local (id_loc, nm_loc) VALUES (1, 'Guichê');
set identity_insert serv_local OFF
go


set identity_insert temas ON
go
INSERT INTO temas (id_tema, nm_tema, desc_tema, autor_tema, dir_tema) VALUES (1, 'Default', 'Tema padrão do SGA Livre', 'DATAPREV', 'sga.default');
set identity_insert temas OFF
go


set identity_insert unidades ON
go
INSERT INTO unidades (id_uni, id_grupo, id_tema, cod_uni, nm_uni, stat_uni) VALUES (1, 1, 1, '1', 'Unidade Padrão', 1);
set identity_insert unidades OFF
go


INSERT INTO senha_uni_msg (id_uni, id_usu, msg_local, msg_global, status_imp) VALUES (1, 1, '', '', 0);
GO

set identity_insert prioridades ON
go
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (1, 'Sem prioridade', 'Sem prioridade', 0, 1);
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (2, 'Def.Auditivo', 'Deficiente Auditivo', 1, 1);
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (3, 'Def.Físico', 'Deficiente Físico', 1, 1);
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (4, 'Def.Visual', 'Deficiente Visual', 1, 1);
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (6, 'Idoso', 'Idosos', 1, 1);
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (5, 'Gestante', 'Gestantes', 1, 1);
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (7, 'Outros', 'Qualquer outra prioridade', 1, 1);
set identity_insert prioridades OFF
go


set identity_insert modulos ON
go
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (7, 'sga.relatorios', 'Relatórios', 'Módulo de relatórios', 'DATAPREV', 'themes/sga.default/imgs/relatorios.png', 1, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (4, 'sga.configuracao', 'Config Global', 'Modulo de Configuração Geral', 'DATAPREV', 'themes/sga.default/imgs/agt_utilities.png', 1, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (1, 'sga.admin', 'Config Unidade', 'Módulo de configuração da unidade', 'DATAPREV', 'themes/sga.default/imgs/unidades.png', 0, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (3, 'sga.monitor', 'Monitor', 'Módulo de monitoração do atendimento', 'DATAPREV', 'themes/sga.default/imgs/monitor_mini.png', 0, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (5, 'sga.triagem', 'Triagem', 'Módulo de distribuição de senhas', 'DATAPREV', 'themes/sga.default/imgs/triagem_mini.png', 0, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (2, 'sga.atendimento', 'Atendimento', 'Módulo de atendimento ao cliente', 'DATAPREV', 'themes/sga.default/imgs/atendimento_mini.png', 0, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (8, 'sga.usuarios', 'Usuários', 'Módulo de gerenciamento de usuários', 'DATAPREV', 'themes/sga.default/imgs/kuser.png', 1, 1);
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (6, 'sga.inicio', 'Inicio', 'Modulo inicial', 'DATAPREV', 'themes/sga.default/imgs/inicio.png', 1, 1);
set identity_insert modulos OFF
go


set identity_insert menus ON
go
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (1, 1, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (2, 1, 'Serviços', 'Gerenciar Serviços da unidade', 'javascript:Admin.alterarConteudo(''monitor/gerenciar_servicos.php'');', 1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (3, 1, 'Config Atendimento', 'Configurações do Atendimento na Unidade', 'javascript:Admin.alterarConteudo(''atendimento/index.php'');', 2, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (4, 1, 'Mensagem/Impressão Local', 'Configurar mensagem/impressão local', 'javascript:Admin.alterarConteudo(''triagem/index.php'');', 3, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (5, 1, 'Sair', 'Sair do sistema', '?logout', 4, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (6, 2, 'Início', 'Ir para a página incial do sistema', '?mod=sga.inicio', 2, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (7, 2, 'Sair', 'Sair do sistema', '?logout', 3, NULL);

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (8, 3, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (9, 3, 'Consultar Senhas', 'Consultar Senhas', 'javascript:Monitor.consultarSenhas();', 1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (10, 3, 'Reativar Senha', 'Reativar Senha', 'javascript:Monitor.reativarSenha();', 2, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (11, 3, 'Cancelar Senha', 'Cancelar Senha', 'javascript:Monitor.cancelarSenha();', 3, NULL);

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (12, 4, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (13, 4, 'Grupos', 'Controle de Grupos do Sistema', 'javascript:Configuracao.alterarConteudo(''grupos/index.php'', ''Configuracao.onLoadGrupos'');', 1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (14, 4, 'Unidades', 'Gerenciar unidades de atendimento', 'javascript:Configuracao.alterarConteudo(''unidades/index.php'');', 2, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (15, 4, 'Cargos', 'Gerenciamento de cargos', 'javascript:Configuracao.alterarConteudo(''cargos/index.php'', ''Configuracao.onLoadCargos'');', 3, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (16, 4, 'Serviços', 'Configuração dos serviços globais', 'javascript:Configuracao.alterarConteudo(''servicos/index.php'');', 4, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (17, 4, 'Config Atendimento', 'Configurações dos Atendimentos relativos a todas unidades', 'javascript:Configuracao.alterarConteudo(''atendimento/index.php'');', 5, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (18, 4, 'Mensagem Global', 'Configurar mensagem global', 'javascript:Configuracao.alterarConteudo(''triagem/index.php'', ''Configuracao.selecionaTexto'');', 6, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (19, 4, 'Sair', 'Sair do sistema', '?logout', 7, NULL);

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (20, 5, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (21, 5, 'Reativar', 'Reativar Senha', 'javascript:Triagem.reativarSenha();', 2, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (22, 5, 'Cancelar', 'Cancelar Senha', 'javascript:Triagem.cancelarSenha();', 3, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (23, 5, 'Sair', 'Sair do sistema', '?logout', 5, NULL);

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (24, 7, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (25, 7, 'Relatórios', 'Relatórios', 'javascript:Relatorios.alterarConteudo(''relatorio/index.php'', ''Relatorios.onLoadGrupos'');', 1, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (26, 7, 'Estatísticas', 'Estatísticas de atendimento', 'javascript:Relatorios.alterarConteudo(''estatisticas/index.php'', ''Relatorios.onLoadGrupos'');', 2, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (27, 7, 'Gráficos', 'Gráficos informativos a partir dos dados de atendimento.', 'javascript:Relatorios.alterarConteudo(''graficos/index.php'', ''Relatorios.onLoadGrupos'');', 3, NULL);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (28, 7, 'Sair', 'Sair do sistema', '?logout', 4, NULL);

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (29, 8, 'Início', 'Ir para a página incial do sistema', '?mod=sga.inicio', 1, 8);
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (30, 8, 'Sair', 'Sair do sistema', '?logout', 2, 8);
set identity_insert menus OFF
go


set identity_insert grupos_aninhados ON
go
INSERT INTO grupos_aninhados (id_grupo, nm_grupo, desc_grupo, esquerda, direita) VALUES (1, 'Raíz', 'Grupo Raíz', 1, 2);
set identity_insert grupos_aninhados OFF
go


/* STORED PROCEDURES */
-----------------------
CREATE PROCEDURE dbo.sp_acumular_atendimentos 
	@p_dt_max datetime
AS
/*
 Move atendimentos da tabela "atendimentos" para a tabela "historico_atendimentos" e todas as
 respectivas codificações da tabela "atend_codif" para a tabela "historico_atend_codif"
 Somente atendimentos com "dt_cheg" anteriores ao parametro(p_dt_max) especificado serão movidos, use now() ou
 uma data no futuro para mover todos os atendimentos existentes
*/
BEGIN TRANSACTION
    -- salva atendimentos
    INSERT INTO historico_atendimentos
    SELECT a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.num_senha, a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli
    FROM atendimentos a
    WHERE dt_cheg <= @p_dt_max

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela historico_atendimentos.', 18,1)
		Rollback Transaction
		return
	end
	
    -- salva atendimentos codificados
    INSERT INTO historico_atend_codif
    SELECT ac.id_atend, ac.id_serv, ac.valor_peso
    FROM atend_codif ac
    WHERE id_atend IN (
        SELECT a.id_atend
        FROM atendimentos a
        WHERE dt_cheg <= @p_dt_max
    )

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela historico_atend_codif.', 18,1)
		Rollback Transaction
		return
	end

    -- limpa atendimentos codificados
    DELETE ac
      FROM atend_codif ac
     WHERE ac.id_atend IN (
        SELECT a.id_atend
        FROM atendimentos a
        WHERE dt_cheg <= @p_dt_max
    )

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao apagar registros na tabela atend_codif.', 18,1)
		Rollback Transaction
		return
	end

    -- limpa atendimentos
    DELETE FROM atendimentos
    WHERE dt_cheg <= @p_dt_max

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao apagar registros na tabela atendimentos.', 18,1)
		Rollback Transaction
		return
	end
COMMIT TRANSACTION
GO


CREATE PROCEDURE dbo.sp_acumular_atendimentos_unidade
	@p_id_uni integer, 
	@p_dt_max datetime
AS
/*
 Equivalente ao sp_acumular_atendimentos(), mas se limita a mover os atendimentos de uma determinada unidade
*/
BEGIN TRANSACTION
    -- salva atendimentos da unidade
    INSERT INTO historico_atendimentos
    SELECT a.id_atend, a.id_uni, a.id_usu, a.id_serv, a.id_pri, a.id_stat, a.num_senha, a.nm_cli, a.num_guiche, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli
    FROM atendimentos a
    WHERE a.dt_cheg <= @p_dt_max
    AND a.id_uni = @p_id_uni

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao apagar registros na tabela historico_atendimentos.', 18,1)
		Rollback Transaction
		return
	end

    -- salva atendimentos codificados da unidade
    INSERT INTO historico_atend_codif
    SELECT ac.id_atend, ac.id_serv, ac.valor_peso
    FROM atend_codif ac
    WHERE id_atend IN (
        SELECT a.id_atend
        FROM atendimentos a
        WHERE dt_cheg <= @p_dt_max
            AND a.id_uni = @p_id_uni
    )

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao apagar registros na tabela historico_atend_codif.', 18,1)
		Rollback Transaction
		return
	end

    -- limpa atendimentos codificados da unidade
    DELETE ac
      FROM atend_codif ac
     WHERE ac.id_atend IN (
        SELECT id_atend
        FROM atendimentos a
        WHERE a.dt_cheg <= @p_dt_max
        AND a.id_uni = @p_id_uni
    )

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao apagar registros na tabela atend_codif.', 18,1)
		Rollback Transaction
		return
	end

    -- limpa atendimentos da unidade
    DELETE a
      FROM atendimentos a
     WHERE dt_cheg <= @p_dt_max
       AND a.id_uni = @p_id_uni

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao apagar registros na tabela atendimentos.', 18,1)
		Rollback Transaction
		return
	end

COMMIT TRANSACTION
GO


CREATE PROCEDURE dbo.sp_atualizar_cargo
	@p_id_cargo integer, 
	@p_id_pai integer, 
	@p_nm_cargo(30) varchar, 
	@p_desc_cargo(140) varchar
AS
/*
-- Atualiza os dados de um cargo
-- Se "p_id_pai" for diferente do atual, efetua uma modificação na arvore, tirando o nó do pai
-- atual, afiliando ao novo pai
*/
DECLARE
    @v_id_pai_atual INTEGER
    ,@v_esq_pai_atual INTEGER
    ,@v_dir_pai_atual INTEGER
    ,@v_esq_cargo INTEGER
    ,@v_dir_cargo INTEGER
    ,@v_len_cargo INTEGER
    ,@v_pai_direita INTEGER
    ,@v_esq_novo_pai INTEGER
    ,@v_dir_novo_pai INTEGER
    ,@v_len_novo_pai INTEGER
    ,@v_deslocamento INTEGER

BEGIN TRANSACTION
    UPDATE cargos_aninhados
    SET nm_cargo = @p_nm_cargo, desc_cargo = @p_desc_cargo
    WHERE id_cargo = @p_id_cargo

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    SELECT TOP 1
         @v_id_pai_atual = pai.id_cargo, 
         @v_esq_pai_atual = pai.esquerda, 
         @v_dir_pai_atual = pai.direita
    FROM cargos_aninhados AS no,
         cargos_aninhados AS pai
    WHERE no.esquerda > pai.esquerda
        AND no.direita < pai.direita
        AND no.id_cargo = @p_id_cargo
    ORDER BY pai.esquerda DESC


    IF @v_id_pai_atual <> @p_id_pai
	BEGIN
        SELECT TOP 1
             @v_esq_cargo = esquerda, 
             @v_dir_cargo = direita, 
             @v_len_cargo = (direita - esquerda + 1)
        FROM cargos_aninhados
        WHERE id_cargo = @p_id_cargo

        SELECT @v_pai_direita = (direita - 1)
        FROM cargos_aninhados
        WHERE id_cargo = @p_id_pai

        UPDATE cargos_aninhados
        SET direita = direita + @v_len_cargo
        WHERE direita > @v_pai_direita

		If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        UPDATE cargos_aninhados
        SET esquerda = esquerda + @v_len_cargo
        WHERE esquerda > @v_pai_direita;

		If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        SELECT TOP 1
             @v_esq_novo_pai = esquerda, 
             @v_dir_novo_pai = direita, 
             @v_len_novo_pai = (direita - esquerda + 1)
        FROM cargos_aninhados
        WHERE id_cargo = @p_id_pai

        SELECT TOP 1
             @v_dir_pai_atual = direita
        FROM cargos_aninhados
        WHERE id_cargo = @v_id_pai_atual

        SELECT TOP 1 
             @v_esq_cargo = esquerda, 
             @v_dir_cargo = direita
        FROM cargos_aninhados
        WHERE id_cargo = @p_id_cargo

        SET @v_deslocamento = @v_dir_novo_pai - @v_dir_cargo - 1

        UPDATE cargos_aninhados
        SET direita = direita + @v_deslocamento,
            esquerda = esquerda + @v_deslocamento
        WHERE esquerda >= @v_esq_cargo
          AND direita <= @v_dir_cargo

		If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        UPDATE cargos_aninhados
        SET direita = direita - @v_len_cargo
        WHERE direita > @v_dir_cargo

		If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        UPDATE cargos_aninhados
        SET esquerda = esquerda - @v_len_cargo 
        WHERE esquerda > @v_dir_cargo

		If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

    END

COMMIT TRANSACTION
GO

--
--
--
CREATE PROCEDURE dbo.sp_atualizar_grupo
	@p_id_grupo integer, 
	@p_id_pai integer, 
	@p_nm_grupo(40) varchar, 
	@p_desc_grupo(150) varchar
AS 
/*
-- Atualiza os dados de um grupo
-- Se "p_id_pai" for diferente do atual, efetua uma modificação na arvore, tirando o nó do pai
-- atual, afiliando ao novo pai
*/
DECLARE
    @v_id_pai_atual INTEGER
    ,@v_esq_pai_atual INTEGER
    ,@v_dir_pai_atual INTEGER
    ,@v_esq_grupo INTEGER
    ,@v_dir_grupo INTEGER
    ,@v_len_grupo INTEGER
    ,@v_pai_direita INTEGER
    ,@v_esq_novo_pai INTEGER
    ,@v_dir_novo_pai INTEGER
    ,@v_len_novo_pai INTEGER
    ,@v_deslocamento INTEGER

BEGIN TRANSACTION
    UPDATE grupos_aninhados
    SET nm_grupo = @p_nm_grupo, desc_grupo = @p_desc_grupo
    WHERE id_grupo = @p_id_grupo

	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao atualizar na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    SELECT TOP 1
         @v_id_pai_atual = pai.id_grupo, 
         @v_esq_pai_atual = pai.esquerda, 
         @v_dir_pai_atual = pai.direita
    FROM grupos_aninhados AS no,
         grupos_aninhados AS pai
    WHERE no.esquerda > pai.esquerda
      AND no.direita < pai.direita
      AND no.id_grupo = @p_id_grupo
    ORDER BY pai.esquerda DESC

    IF @v_id_pai_atual <> @p_id_pai
	BEGIN
        SELECT TOP 1 
             @v_esq_grupo = esquerda, 
             @v_dir_grupo = direita, 
             @v_len_grupo = (direita - esquerda + 1)
        FROM grupos_aninhados
        WHERE id_grupo = @p_id_grupo

        SELECT @v_pai_direita = (direita - 1)
        FROM grupos_aninhados
        WHERE id_grupo = @p_id_pai

        UPDATE grupos_aninhados
        SET direita = direita + @v_len_grupo
        WHERE direita > @v_pai_direita

	 	If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela grupos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        UPDATE grupos_aninhados
        SET esquerda = esquerda + @v_len_grupo
        WHERE esquerda > @v_pai_direita

	 	If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela grupos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        SELECT TOP 1
             @v_esq_novo_pai = esquerda, 
             @v_dir_novo_pai = direita, 
             @v_len_novo_pai = (direita - esquerda + 1)
        FROM grupos_aninhados
        WHERE id_grupo = @p_id_pai

        SELECT TOP 1 
		     @v_dir_pai_atual = direita
        FROM grupos_aninhados
        WHERE id_grupo = @v_id_pai_atual

        SELECT TOP 1
             @v_esq_grupo = esquerda, 
             @v_dir_grupo = direita
        FROM grupos_aninhados
        WHERE id_grupo = @p_id_grupo

        SET @v_deslocamento = @v_dir_novo_pai - @v_dir_grupo - 1

        UPDATE grupos_aninhados
        SET direita = direita + @v_deslocamento,
            esquerda = esquerda + @v_deslocamento
        WHERE esquerda >= @v_esq_grupo
          AND direita <= @v_dir_grupo

	 	If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela grupos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        UPDATE grupos_aninhados
        SET direita = direita - @v_len_grupo
        WHERE direita > @v_dir_grupo

	 	If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela grupos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

        UPDATE grupos_aninhados
        SET esquerda = esquerda - @v_len_grupo 
        WHERE esquerda > @v_dir_grupo

	 	If @@ERROR <> 0 Begin
			Raiserror ( 'Erro ao atualizar na tabela grupos_aninhados.', 18,1)
			Rollback Transaction
			return
		end

    END

COMMIT TRANSACTION
GO

--
-- Retorna a lotação mais próxima do usuário que da acesso ao grupo especificado
--
--
CREATE PROCEDURE dbo.sp_get_lotacao_valida
	@p_id_usu integer, 
	@p_in_id_grupo integer, 
	@p_id_grupo integer OUTPUT,
	@p_id_cargo integer OUTPUT
AS
/*
-- Se o usuário estiver lotado no grupo "p_in_id_grupo", esta lotação é retornada
-- Caso contrário, o pai direto/indireto mais próximo onde o usuario estiver lotado será retornado.
-- Desta forma, um usuário que está lotado na raiz sempre possui uma lotação válida para qualquer
-- grupo.
*/
DECLARE
    @v_uni_grupo_esq INTEGER,
    @v_uni_grupo_dir INTEGER
BEGIN
    SELECT TOP 1 
         @v_uni_grupo_esq = esquerda, 
         @v_uni_grupo_dir = direita
    FROM grupos_aninhados
    WHERE id_grupo = @p_in_id_grupo

    SELECT TOP 1
         @p_id_cargo = ugc.id_cargo, 
         @p_id_grupo = ugc.id_grupo
    FROM usu_grup_cargo ugc
    INNER JOIN grupos_aninhados ga
        ON (ugc.id_grupo = ga.id_grupo)
    WHERE id_usu = @p_id_usu
      AND esquerda <= @v_uni_grupo_esq
      AND direita >= @v_uni_grupo_dir
    ORDER BY esquerda DESC
END
GO


--
-- TOC entry 21 (class 1255 OID 28269)
-- Dependencies: 3 400
-- Name: sp_inserir_cargo(integer, varchar, varchar); Type: FUNCTION; Schema: public; Owner: -
--

CREATE PROCEDURE dbo.sp_inserir_cargo
	@p_pai_id integer, 
	@p_nm_cargo(30) varchar, 
	@p_desc_cargo(140) varchar
AS
/*
-- Insere um nó na arvore de "cargos_aninhados"
*/
DECLARE
    @v_pai_direita INTEGER

BEGIN TRANSACTION

    SELECT @v_pai_direita = (direita - 1)
    FROM cargos_aninhados
    WHERE id_cargo = @p_pai_id

    UPDATE cargos_aninhados
    SET direita = direita + 2
    WHERE direita > @v_pai_direita

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    UPDATE cargos_aninhados
    SET esquerda = esquerda + 2
    WHERE esquerda > @v_pai_direita

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao atualizar na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    INSERT INTO cargos_aninhados 
          (nm_cargo, desc_cargo, esquerda, direita)
    VALUES(@p_nm_cargo, @p_desc_cargo, @v_pai_direita + 1, @v_pai_direita + 2)

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

COMMIT TRANSACTION
GO

--
-- TOC entry 47 (class 1255 OID 27611)
-- Dependencies: 400 3
-- Name: sp_inserir_grupo(integer, varchar, varchar); Type: FUNCTION; Schema: public; Owner: -
--
CREATE PROCEDURE dbo.sp_inserir_grupo
	@p_pai_id integer, 
	@p_nm_grupo(40) varchar, 
	@p_desc_grupo(150) varchar
AS 
/*
-- Insere um grupo na arvore de "grupos_aninhados"
*/
DECLARE
    @v_pai_direita INTEGER;
BEGIN TRANSACTION
    -- Obtem o valor "direita" do nó pai
    SELECT @v_pai_direita =(direita - 1)
    FROM grupos_aninhados
    WHERE id_grupo = @p_pai_id

    -- Desloca todos elementos da arvore, para a direita (+2), abrindo um espaço de 2
    -- a ser usado apra inserir o nó
    UPDATE grupos_aninhados
    SET direita = direita + 2
    WHERE direita > @v_pai_direita
    
 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    -- continuação do deslocamento acima (agora para o "esquerda")
    UPDATE grupos_aninhados
    SET esquerda = esquerda + 2
    WHERE esquerda > @v_pai_direita

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    -- Insere o nó no espaço que foi aberto
    INSERT INTO grupos_aninhados(nm_grupo, desc_grupo, esquerda, direita)
    VALUES(@p_nm_grupo, @p_desc_grupo, @v_pai_direita + 1, @v_pai_direita + 2)

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

COMMIT TRANSACTION
GO

--
-- TOC entry 22 (class 1255 OID 28270)
-- Dependencies: 400 3
-- Name: sp_remover_cargo_cascata(integer); Type: FUNCTION; Schema: public; Owner: -
--
CREATE PROCEDURE dbo.sp_remover_cargo_cascata
	@p_id_cargo integer
AS 
/*
-- Remove um cargo, e seus filhos indiretos/diretos.
-- Suponha a Hierarquia: Presidente > Diretor > Gerente > Estágiario
-- Remover "Diretor" irá também remover Gerente e Estágiario
*/
DECLARE
    @v_esquerda INTEGER
    ,@v_direita INTEGER
    ,@v_tamanho INTEGER

BEGIN TRANSACTION

    SELECT 
         @v_esquerda = esquerda, 
         @v_direita = direita, 
         @v_tamanho = direita - esquerda + 1
    FROM cargos_aninhados
    WHERE id_cargo = @p_id_cargo

    DELETE FROM cargos_aninhados
    WHERE esquerda BETWEEN @v_esquerda AND @v_direita

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    UPDATE cargos_aninhados
    SET direita = (direita - @v_tamanho)
    WHERE direita > @v_direita;

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

    UPDATE cargos_aninhados
    SET esquerda = (esquerda - @v_tamanho)
    WHERE esquerda > @v_direita;

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela cargos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

COMMIT TRANSACTION
GO


--
--
CREATE PROCEDURE dbo.sp_remover_grupo_cascata
	@p_id_grupo integer
AS
/*
-- Remove um grupo e seus filhos diretos/indiretos.
--
-- Exemplo: Brasil > Espírito Santo > Vitória
-- Remover "Espírito Santo" irá tambem remover "Vitória"
*/
DECLARE
    @v_esquerda INTEGER
    ,@v_direita INTEGER
    ,@v_tamanho INTEGER

BEGIN TRANSACTION

	SELECT 
	     @v_esquerda = esquerda, 
	     @v_direita = direita, 
	     @v_tamanho = direita - esquerda + 1
	FROM grupos_aninhados
	WHERE id_grupo = @p_id_grupo;

	DELETE FROM grupos_aninhados
	WHERE esquerda BETWEEN @v_esquerda AND @v_direita;

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

	UPDATE grupos_aninhados
	SET direita = (direita - @v_tamanho)
	WHERE direita > @v_direita;

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

	UPDATE grupos_aninhados
	SET esquerda = (esquerda - @v_tamanho)
	WHERE esquerda > @v_direita;

 	If @@ERROR <> 0 Begin
		Raiserror ( 'Erro ao inserir na tabela grupos_aninhados.', 18,1)
		Rollback Transaction
		return
	end

COMMIT TRANSACTION
GO

--
--
CREATE PROCEDURE dbo.sp_salvar_session_id
	@p_id_usu integer, 
	@p_session_id varchar(40)
AS
/*
-- Insere uma session, caso não exista, ou atualiza caso exista.
-- Equivalente ao REPLACE do MySQL
*/
BEGIN
    IF EXISTS( SELECT 1 FROM usu_session WHERE id_usu = @p_id_usu )
        UPDATE usu_session
        SET session_id = @p_session_id
        WHERE id_usu = @p_id_usu;
    ELSE
        INSERT INTO usu_session VALUES( @p_id_usu, @p_session_id, 1 );
END
GO


