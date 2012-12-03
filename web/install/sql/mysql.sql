-- @adapter=MySQL
-- @author=unknown
-- @date=unknown

--
-- MySQL database
--

CREATE TABLE atend_codif (
    id_atend bigint NOT NULL AUTO_INCREMENT,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL,
	PRIMARY KEY (id_atend, id_serv) 
) ENGINE = INNODB;--

CREATE TABLE atend_status (
    id_stat integer NOT NULL AUTO_INCREMENT,
    nm_stat varchar(30) NOT NULL,
    desc_stat varchar (150) NOT NULL,
	PRIMARY KEY (`id_stat`)
) ENGINE = INNODB;--


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
) ENGINE = INNODB;--


CREATE TABLE cargos_aninhados (
    id_cargo integer NOT NULL AUTO_INCREMENT,
    nm_cargo varchar(30) NOT NULL,
    desc_cargo varchar(140),
    esquerda integer NOT NULL,
    direita integer NOT NULL,
	PRIMARY KEY (id_cargo)
) ENGINE = INNODB;--


CREATE TABLE cargos_mod_perm (
    id_cargo integer NOT NULL,
    id_mod integer NOT NULL,
    permissao integer NOT NULL,
	PRIMARY KEY (id_cargo, id_mod)
) ENGINE = INNODB;--


CREATE TABLE grupos_aninhados (
    id_grupo integer NOT NULL AUTO_INCREMENT,
    nm_grupo varchar(40) NOT NULL,
    desc_grupo varchar(150) NOT NULL,
    esquerda integer NOT NULL,
    direita integer NOT NULL,
       PRIMARY KEY (id_grupo)
) ENGINE = INNODB;--


CREATE TABLE historico_atend_codif (
    id_atend bigint NOT NULL,
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL,
	PRIMARY KEY (id_atend, id_serv)
) ENGINE = INNODB;--


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
) ENGINE = INNODB;--


CREATE TABLE menus (
    id_menu integer NOT NULL AUTO_INCREMENT,
    id_mod integer NOT NULL,
    nm_menu varchar(50) NOT NULL,
    desc_menu varchar(100) NOT NULL,
    lnk_menu varchar(150) NOT NULL,
    ord_menu smallint NOT NULL,
    id_mod_impl integer,
	PRIMARY KEY (id_menu)
) ENGINE = INNODB;--


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
) ENGINE = INNODB;--

CREATE TABLE paineis (
    id_uni integer NOT NULL,
    host integer NOT NULL,
	PRIMARY KEY (host)
) ENGINE = INNODB;--

CREATE TABLE paineis_servicos (
    host integer NOT NULL,
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
	PRIMARY KEY (host, id_serv)
) ENGINE = INNODB;--


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
) ENGINE = INNODB;--


CREATE TABLE prioridades (
    id_pri integer NOT NULL AUTO_INCREMENT,
    nm_pri varchar(30) NOT NULL,
    desc_pri varchar(100) NOT NULL,
    peso_pri smallint NOT NULL,
    stat_pri smallint NOT NULL,
	PRIMARY KEY (id_pri)
) ENGINE = INNODB;--


CREATE TABLE senha_uni_msg (
    id_uni integer NOT NULL,
    id_usu integer NOT NULL,
    msg_local varchar(100) DEFAULT NULL,
    msg_global varchar(100),
    status_imp integer DEFAULT 0,
	PRIMARY KEY (id_uni)
) ENGINE = INNODB;--

CREATE TABLE serv_local (
    id_loc integer NOT NULL AUTO_INCREMENT,
    nm_loc varchar(20) NOT NULL,
	PRIMARY KEY (id_loc)
) ENGINE = INNODB;--

CREATE TABLE serv_peso (
    id_serv integer NOT NULL,
    valor_peso smallint NOT NULL,
	PRIMARY KEY (id_serv)
) ENGINE = INNODB;--

CREATE TABLE servicos (
    id_serv integer NOT NULL AUTO_INCREMENT,
    id_macro integer,
    desc_serv varchar(100) NOT NULL,
    nm_serv varchar(50),
    stat_serv smallint,
	PRIMARY KEY (id_serv)
) ENGINE = INNODB;--


CREATE TABLE temas (
    id_tema integer NOT NULL AUTO_INCREMENT,
    nm_tema varchar(25) NOT NULL,
    desc_tema varchar(100) NOT NULL,
    autor_tema varchar(25) NOT NULL,
    dir_tema varchar(50) NOT NULL,
	PRIMARY KEY (id_tema)
) ENGINE = INNODB;--


CREATE TABLE uni_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_loc integer NOT NULL,
    nm_serv varchar(50) NOT NULL,
    sigla_serv char(1) NOT NULL,
    stat_serv smallint NOT NULL,
	PRIMARY KEY (id_uni, id_serv)
) ENGINE = INNODB;--


CREATE TABLE unidades (
    id_uni integer NOT NULL AUTO_INCREMENT,
    id_grupo integer NOT NULL,
    id_tema integer NOT NULL,
    cod_uni varchar(10) NOT NULL,
    nm_uni varchar(50) DEFAULT NULL,
    stat_uni smallint DEFAULT 1,
	PRIMARY KEY (id_uni)
) ENGINE = INNODB;--


CREATE TABLE usu_grup_cargo (
    id_usu integer NOT NULL,
    id_grupo integer NOT NULL,
    id_cargo integer NOT NULL,
	PRIMARY KEY (id_usu, id_grupo)
) ENGINE = INNODB;--


CREATE TABLE usu_serv (
    id_uni integer NOT NULL,
    id_serv integer NOT NULL,
    id_usu integer NOT NULL,
	PRIMARY KEY (id_uni, id_serv, id_usu)
) ENGINE = INNODB;--

CREATE TABLE usu_session (
    id_usu integer NOT NULL,
    session_id varchar(40) NOT NULL,
    stat_session integer NOT NULL,
	PRIMARY KEY (id_usu)
) ENGINE = INNODB;--

CREATE TABLE usuarios (
    id_usu integer NOT NULL AUTO_INCREMENT,
    login_usu varchar(20) NOT NULL,
    nm_usu varchar(20) NOT NULL,
    ult_nm_usu varchar(100) NOT NULL,
    senha_usu varchar(40) NOT NULL,
    ult_acesso datetime,
    stat_usu smallint NOT NULL,
	PRIMARY KEY (id_usu)
) ENGINE = INNODB;--

CREATE TABLE variaveis_sistema (
    chave varchar(20) NOT NULL,
    valor varchar(128) NOT NULL,
	PRIMARY KEY (chave)
) ENGINE = INNODB;--

CREATE VIEW view_historico_atend_codif AS
    SELECT atend_codif.id_atend, atend_codif.id_serv, atend_codif.valor_peso FROM atend_codif UNION ALL SELECT historico_atend_codif.id_atend, historico_atend_codif.id_serv, historico_atend_codif.valor_peso FROM historico_atend_codif;--

CREATE VIEW view_historico_atendimentos AS
    SELECT atendimentos.id_atend, atendimentos.id_uni, atendimentos.id_usu, atendimentos.id_serv, atendimentos.id_pri, atendimentos.id_stat, atendimentos.num_senha, atendimentos.nm_cli, atendimentos.num_guiche, atendimentos.dt_cheg, atendimentos.dt_cha, atendimentos.dt_ini, atendimentos.dt_fim, atendimentos.ident_cli FROM atendimentos UNION ALL SELECT historico_atendimentos.id_atend, historico_atendimentos.id_uni, historico_atendimentos.id_usu, historico_atendimentos.id_serv, historico_atendimentos.id_pri, historico_atendimentos.id_stat, historico_atendimentos.num_senha, historico_atendimentos.nm_cli, historico_atendimentos.num_guiche, historico_atendimentos.dt_cheg, historico_atendimentos.dt_cha, historico_atendimentos.dt_ini, historico_atendimentos.dt_fim, historico_atendimentos.ident_cli FROM historico_atendimentos;--

INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (1, 'Passou pela Triagem', 'Cliente chegou na unidade e foi atendido pela Triagem');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (3, 'Atendimento Iniciado', 'Cliente chegou no guiche e esta sendo atendido');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (5, 'Nao Compareceu', 'Apos chamar o proximo da fila, o mesmo nao compareceu');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (6, 'Cancelada', 'Senha Cancelada por algum motivo');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (7, 'Erro de triagem', 'Responsavel pela Triagem emitiu a senha para atendimento errado');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (9, 'Agendamento não confirmado', '');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (2, 'Chamado pela mesa', 'Atendente chamou o proximo da fila');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (4, 'Atendimento Encerrado', 'Atendente encerrou o atendimento, mas ainda não codificou os serviços atendidos.');--
INSERT INTO atend_status (id_stat, nm_stat, desc_stat) VALUES (8, 'Atendimento Codificado', 'Atendimento finalizado.');--

INSERT INTO cargos_aninhados (id_cargo, nm_cargo, desc_cargo, esquerda, direita) VALUES (1, 'Administrador', 'Administrador geral do sistema', 1, 2);--

INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 1, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 2, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 3, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 4, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 5, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 6, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 7, 3);--
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 8, 3);--
INSERT INTO grupos_aninhados (id_grupo, nm_grupo, desc_grupo, esquerda, direita) VALUES (1, 'Raíz', 'Grupo Raíz', 1, 2);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (1, 1, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (2, 1, 'Serviços', 'Gerenciar Serviços da unidade', 'javascript:Admin.alterarConteudo(''monitor/gerenciar_servicos.php'');', 1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (3, 1, 'Config Atendimento', 'Configurações do Atendimento na Unidade', 'javascript:Admin.alterarConteudo(''atendimento/index.php'');', 2, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (4, 1, 'Mensagem/Impressão Local', 'Configurar mensagem/impressão local', 'javascript:Admin.alterarConteudo(''triagem/index.php'');', 3, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (5, 1, 'Sair', 'Sair do sistema', '?logout', 4, NULL);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (6, 2, 'Início', 'Ir para a página incial do sistema', '?mod=sga.inicio', 2, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (7, 2, 'Sair', 'Sair do sistema', '?logout', 3, NULL);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (8, 3, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (9, 3, 'Consultar Senhas', 'Consultar Senhas', 'javascript:Monitor.consultarSenhas();', 1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (10, 3, 'Reativar Senha', 'Reativar Senha', 'javascript:Monitor.reativarSenha();', 2, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (11, 3, 'Cancelar Senha', 'Cancelar Senha', 'javascript:Monitor.cancelarSenha();', 3, NULL);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (12, 4, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (13, 4, 'Grupos', 'Controle de Grupos do Sistema', 'javascript:Configuracao.alterarConteudo(''grupos/index.php'', ''Configuracao.onLoadGrupos'');', 1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (14, 4, 'Unidades', 'Gerenciar unidades de atendimento', 'javascript:Configuracao.alterarConteudo(''unidades/index.php'');', 2, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (15, 4, 'Cargos', 'Gerenciamento de cargos', 'javascript:Configuracao.alterarConteudo(''cargos/index.php'', ''Configuracao.onLoadCargos'');', 3, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (16, 4, 'Serviços', 'Configuração dos serviços globais', 'javascript:Configuracao.alterarConteudo(''servicos/index.php'');', 4, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (17, 4, 'Config Atendimento', 'Configurações dos Atendimentos relativos a todas unidades', 'javascript:Configuracao.alterarConteudo(''atendimento/index.php'');', 5, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (18, 4, 'Mensagem Global', 'Configurar mensagem global', 'javascript:Configuracao.alterarConteudo(''triagem/index.php'', ''Configuracao.selecionaTexto'');', 6, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (19, 4, 'Sair', 'Sair do sistema', '?logout', 7, NULL);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (20, 5, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (21, 5, 'Reativar', 'Reativar Senha', 'javascript:Triagem.reativarSenha();', 2, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (22, 5, 'Cancelar', 'Cancelar Senha', 'javascript:Triagem.cancelarSenha();', 3, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (23, 5, 'Sair', 'Sair do sistema', '?logout', 5, NULL);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (24, 7, 'Início', 'Retorna para a página incial.', '?mod=sga.inicio', -1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (25, 7, 'Relatórios', 'Relatórios', 'javascript:Relatorios.alterarConteudo(''relatorio/index.php'', ''Relatorios.onLoadGrupos'');', 1, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (26, 7, 'Estatísticas', 'Estatísticas de atendimento', 'javascript:Relatorios.alterarConteudo(''estatisticas/index.php'', ''Relatorios.onLoadGrupos'');', 2, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (27, 7, 'Gráficos', 'Gráficos informativos a partir dos dados de atendimento.', 'javascript:Relatorios.alterarConteudo(''graficos/index.php'', ''Relatorios.onLoadGrupos'');', 3, NULL);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (28, 7, 'Sair', 'Sair do sistema', '?logout', 4, NULL);--

INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (29, 8, 'Início', 'Ir para a página incial do sistema', '?mod=sga.inicio', 1, 8);--
INSERT INTO menus (id_menu, id_mod, nm_menu, desc_menu, lnk_menu, ord_menu, id_mod_impl) VALUES (30, 8, 'Sair', 'Sair do sistema', '?logout', 2, 8);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (7, 'sga.relatorios', 'Relatórios', 'Módulo de relatórios', 'DATAPREV', 'themes/sga.default/imgs/relatorios.png', 1, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (4, 'sga.configuracao', 'Config Global', 'Modulo de Configuração Geral', 'DATAPREV', 'themes/sga.default/imgs/agt_utilities.png', 1, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (1, 'sga.admin', 'Config Unidade', 'Módulo de configuração da unidade', 'DATAPREV', 'themes/sga.default/imgs/unidades.png', 0, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (3, 'sga.monitor', 'Monitor', 'Módulo de monitoração do atendimento', 'DATAPREV', 'themes/sga.default/imgs/monitor_mini.png', 0, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (5, 'sga.triagem', 'Triagem', 'Módulo de distribuição de senhas', 'DATAPREV', 'themes/sga.default/imgs/triagem_mini.png', 0, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (2, 'sga.atendimento', 'Atendimento', 'Módulo de atendimento ao cliente', 'DATAPREV', 'themes/sga.default/imgs/atendimento_mini.png', 0, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (8, 'sga.usuarios', 'Usuários', 'Módulo de gerenciamento de usuários', 'DATAPREV', 'themes/sga.default/imgs/kuser.png', 1, 1);--
INSERT INTO modulos (id_mod, chave_mod, nm_mod, desc_mod, autor_mod, img_mod, tipo_mod, stat_mod) VALUES (6, 'sga.inicio', 'Inicio', 'Modulo inicial', 'DATAPREV', 'themes/sga.default/imgs/inicio.png', 1, 1);--

INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (1, 'Sem prioridade', 'Sem prioridade', 0, 1);--
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (2, 'Def.Auditivo', 'Deficiente Auditivo', 1, 1);--
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (3, 'Def.Físico', 'Deficiente Físico', 1, 1);--
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (4, 'Def.Visual', 'Deficiente Visual', 1, 1);--
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (6, 'Idoso', 'Idosos', 1, 1);--
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (5, 'Gestante', 'Gestantes', 1, 1);--
INSERT INTO prioridades (id_pri, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (7, 'Outros', 'Qualquer outra prioridade', 1, 1);--

INSERT INTO senha_uni_msg (id_uni, id_usu, msg_local, msg_global, status_imp) VALUES (1, 1, '', '', 0);--

INSERT INTO serv_local (id_loc, nm_loc) VALUES (1, 'Mesa');--

INSERT INTO temas (id_tema, nm_tema, desc_tema, autor_tema, dir_tema) VALUES (1, 'Default', 'Tema padrão do SGA Livre', 'DATAPREV', 'sga.default');--

INSERT INTO unidades (id_uni, id_grupo, id_tema, cod_uni, nm_uni, stat_uni) VALUES (1, 1, 1, '1', 'Unidade Padrão', 1);--

INSERT INTO usu_grup_cargo (id_usu, id_grupo, id_cargo) VALUES (1, 1, 1);--

INSERT INTO usuarios (id_usu, login_usu, nm_usu, ult_nm_usu, senha_usu, ult_acesso, stat_usu) VALUES (1, '%login_usu%', '%nm_usu%', '%ult_nm_usu%', '%senha_usu%', NULL, 1);--

ALTER TABLE temas
    ADD CONSTRAINT dir_temas UNIQUE (dir_tema);--


CREATE UNIQUE INDEX cod_uni ON unidades (cod_uni);--


CREATE INDEX direita ON grupos_aninhados (direita);--

CREATE INDEX esqdir ON grupos_aninhados (esquerda, direita);--


CREATE INDEX esquerda ON grupos_aninhados (esquerda);--

CREATE INDEX fki_atend_codif_ibfk_2 ON atend_codif (id_serv);--

CREATE INDEX fki_atendimentos_ibfk_1 ON atendimentos (id_pri);--

CREATE INDEX fki_atendimentos_ibfk_2 ON atendimentos (id_uni, id_serv);--

CREATE INDEX fki_atendimentos_ibfk_3 ON atendimentos (id_stat);--

CREATE INDEX fki_atendimentos_ibfk_4 ON atendimentos (id_usu);--

CREATE INDEX fki_id_grupo ON unidades (id_grupo);--

CREATE INDEX fki_menus_ibfk_1 ON menus (id_mod);--

CREATE INDEX fki_servicos_ibfk_1 ON servicos (id_macro);--

CREATE INDEX fki_uni_serv_ibfk_2 ON uni_serv (id_serv);--

CREATE INDEX fki_uni_serv_ibfk_3 ON uni_serv (id_loc);--

CREATE INDEX fki_unidades_ibfk_1 ON unidades (id_tema);--

CREATE INDEX fki_usu_serv_ibfk_1 ON usu_serv (id_serv, id_uni);--

CREATE INDEX fki_usu_serv_ibfk_2 ON usu_serv (id_usu);--

CREATE UNIQUE INDEX local_serv_nm ON serv_local (nm_loc);--


CREATE UNIQUE INDEX login_usu ON usuarios (login_usu);--

CREATE UNIQUE INDEX modulos_chave ON modulos (chave_mod);--

ALTER TABLE atend_codif
    ADD CONSTRAINT atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE atend_codif
    ADD CONSTRAINT atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE atendimentos
    ADD CONSTRAINT atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE atendimentos
    ADD CONSTRAINT atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE atendimentos
    ADD CONSTRAINT atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE atendimentos
    ADD CONSTRAINT atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT cargos_mod_perm_ibfk_1 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE cargos_mod_perm
    ADD CONSTRAINT cargos_mod_perm_ibfk_2 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE historico_atend_codif
    ADD CONSTRAINT historico_atend_codif_ibfk_1 FOREIGN KEY (id_atend) REFERENCES historico_atendimentos(id_atend) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE historico_atend_codif
    ADD CONSTRAINT historico_atend_codif_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_1 FOREIGN KEY (id_pri) REFERENCES prioridades(id_pri) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv(id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_3 FOREIGN KEY (id_stat) REFERENCES atend_status(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE historico_atendimentos
    ADD CONSTRAINT historico_atendimentos_ibfk_4 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE menus
    ADD CONSTRAINT menus_ibfk_1 FOREIGN KEY (id_mod) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE menus
    ADD CONSTRAINT menus_ibfk_2 FOREIGN KEY (id_mod_impl) REFERENCES modulos(id_mod) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE paineis
    ADD CONSTRAINT paineis_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE paineis_servicos
    ADD CONSTRAINT paineis_servicos_ibfk_1 FOREIGN KEY (host) REFERENCES paineis (host) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE paineis_servicos
    ADD CONSTRAINT paineis_servicos_ibfk_2 FOREIGN KEY (id_uni, id_serv) REFERENCES uni_serv (id_uni, id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE painel_senha
    ADD CONSTRAINT painel_senha_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE painel_senha
    ADD CONSTRAINT painel_senha_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE serv_peso
    ADD CONSTRAINT peso_ibfk_1 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE senha_uni_msg
    ADD CONSTRAINT senha_uni_msg_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE servicos
    ADD CONSTRAINT servicos_ibfk_1 FOREIGN KEY (id_macro) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE uni_serv
    ADD CONSTRAINT uni_serv_ibfk_1 FOREIGN KEY (id_uni) REFERENCES unidades(id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE uni_serv
    ADD CONSTRAINT uni_serv_ibfk_2 FOREIGN KEY (id_serv) REFERENCES servicos(id_serv) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE uni_serv
    ADD CONSTRAINT uni_serv_ibfk_3 FOREIGN KEY (id_loc) REFERENCES serv_local(id_loc) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE unidades
    ADD CONSTRAINT unidades_ibfk_1 FOREIGN KEY (id_tema) REFERENCES temas(id_tema) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE unidades
    ADD CONSTRAINT unidades_id_grupo_fkey FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;--
ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_ibfk_2 FOREIGN KEY (id_grupo) REFERENCES grupos_aninhados(id_grupo) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE usu_grup_cargo
    ADD CONSTRAINT usu_grup_cargo_ibfk_3 FOREIGN KEY (id_cargo) REFERENCES cargos_aninhados(id_cargo) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE usu_serv
    ADD CONSTRAINT usu_serv_ibfk_1 FOREIGN KEY (id_serv, id_uni) REFERENCES uni_serv(id_serv, id_uni) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE usu_serv
    ADD CONSTRAINT usu_serv_ibfk_2 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;--

ALTER TABLE usu_session
    ADD CONSTRAINT usu_session_ibfk_1 FOREIGN KEY (id_usu) REFERENCES usuarios(id_usu) ON UPDATE RESTRICT ON DELETE RESTRICT;--

