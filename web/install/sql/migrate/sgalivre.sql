
-- postgres migration script
-- @author rogeriolino

-- unidades
ALTER TABLE unidades DROP COLUMN id_tema;
ALTER TABLE unidades ADD COLUMN stat_imp smallint DEFAULT 0;
ALTER TABLE unidades ADD COLUMN msg_imp varchar(100);
UPDATE unidades u SET stat_imp = s.status_imp, msg_imp = s.msg_local FROM senha_uni_msg s WHERE s.id_uni = u.id_uni;

-- modulos
ALTER TABLE modulos DROP COLUMN img_mod;
-- atualizando modulos
UPDATE modulos SET chave_mod = 'sga.unidade', nm_mod = 'Configuração', desc_mod = 'Módulo para gerenciamento da unidade atual', autor_mod = 'rogeriolino' WHERE id_mod = 1;
UPDATE modulos SET chave_mod = 'sga.atendimento', nm_mod = 'Atendimento', desc_mod = 'Efetue o atendimento às senhas distribuídas dos serviços que você atende', autor_mod = 'rogeriolino' WHERE id_mod = 2;
UPDATE modulos SET chave_mod = 'sga.monitor', nm_mod = 'Monitor', desc_mod = 'Gerencie as senhas aguardando atendimento', autor_mod = 'rogeriolino' WHERE id_mod = 3;
UPDATE modulos SET chave_mod = 'sga.admin', nm_mod = 'Administração', desc_mod = 'Configurações gerais do sistema', autor_mod = 'rogeriolino' WHERE id_mod = 4;
UPDATE modulos SET chave_mod = 'sga.triagem', nm_mod = 'Triagem', desc_mod = 'Gerencie a distribuíção das senhas da unidade atual', autor_mod = 'rogeriolino' WHERE id_mod = 5;
UPDATE modulos SET chave_mod = 'sga.servicos', nm_mod = 'Serviços', desc_mod = 'Gerencie os serviços do sistema', autor_mod = 'rogeriolino' WHERE id_mod = 6;
UPDATE modulos SET chave_mod = 'sga.estatisticas', nm_mod = 'Estatísticas', desc_mod = 'Visualize e exporte estastísticas e relatórios sobre o sistema', autor_mod = 'rogeriolino' WHERE id_mod = 7;
UPDATE modulos SET chave_mod = 'sga.usuarios', nm_mod = 'Usuários', desc_mod = 'Gerencie os usuários do sistema', autor_mod = 'rogeriolino' WHERE id_mod = 8;
-- inserindo novos modulos
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.prioridades', 'Prioridades', 'Gerencie os prioridades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.grupos', 'Grupos', 'Gerencie os grupos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.unidades', 'Unidades', 'Gerencie as unidades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.cargos', 'Cargos', 'Gerencie os cargos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.modulos', 'Módulos', 'Gerencie os módulos instalados', 'rogeriolino', 1, 1);
-- permissoes para os novos modulos
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, (SELECT id_mod FROM modulos WHERE chave_mod = 'sga.prioridades'), 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, (SELECT id_mod FROM modulos WHERE chave_mod = 'sga.grupos'), 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, (SELECT id_mod FROM modulos WHERE chave_mod = 'sga.unidades'), 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, (SELECT id_mod FROM modulos WHERE chave_mod = 'sga.cargos'), 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, (SELECT id_mod FROM modulos WHERE chave_mod = 'sga.modulos'), 3);

-- config
CREATE TABLE config (
    chave varchar(150) NOT NULL,
    valor TEXT NOT NULL,
    tipo integer NOT NULL,
    PRIMARY KEY (chave)
);

-- usuarios
ALTER TABLE usuarios ADD COLUMN session_id varchar(40) NOT NULL DEFAULT '';

-- atendimentos
ALTER TABLE atendimentos ADD COLUMN sigla_senha VARCHAR(1);
UPDATE atendimentos a SET sigla_senha = s.sigla_serv FROM uni_serv s WHERE s.id_uni = a.id_uni AND s.id_serv = a.id_serv;
ALTER TABLE atendimentos ALTER COLUMN sigla_senha SET NOT NULL;
ALTER TABLE historico_atendimentos ADD COLUMN sigla_senha VARCHAR(1);
UPDATE historico_atendimentos a SET sigla_senha = s.sigla_serv FROM uni_serv s WHERE s.id_uni = a.id_uni AND s.id_serv = a.id_serv;
ALTER TABLE historico_atendimentos ALTER COLUMN sigla_senha SET NOT NULL;

DROP TABLE menus;
DROP TABLE senha_uni_msg;
DROP TABLE temas;
DROP TABLE usu_session;
DROP TABLE variaveis_sistema;
