
-- modulos globais
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.locais', 'Locais', 'Gerencie os locais de atendimento', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.prioridades', 'Prioridades', 'Gerencie os prioridades do sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.servicos', 'Serviços', 'Gerencie os serviços do sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.grupos', 'Grupos', 'Gerencie os grupos do sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.unidades', 'Unidades', 'Gerencie as unidades do sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.cargos', 'Cargos', 'Gerencie os cargos do sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.usuarios', 'Usuários', 'Gerencie os usuários do sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.modulos', 'Módulos', 'Gerencie os módulos instalados', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.admin', 'Administração', 'Configurações gerais do sistema', 1, 1);
-- modulos locais
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.triagem', 'Triagem', 'Gerencie a distribuíção das senhas da unidade atual', 0, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.monitor', 'Monitor', 'Gerencie as senhas aguardando atendimento', 0, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.atendimento', 'Atendimento', 'Efetue o atendimento às senhas distribuídas dos serviços que você atende', 0, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.estatisticas', 'Estatísticas', 'Visualize e exporte estastísticas e relatórios sobre o sistema', 1, 1);
INSERT INTO modulos (chave, nome, descricao, tipo, status) VALUES ('sga.unidade', 'Configuração', 'Módulo para gerenciamento da unidade atual', 0, 1);

-- prioridades
INSERT INTO prioridades (nome, descricao, peso, status) VALUES ('Sem prioridade', 'Atendimento normal', 0, 1);
INSERT INTO prioridades (nome, descricao, peso, status) VALUES ('Portador de Deficiência', 'Atendimento prioritáro para portadores de deficiência', 1, 1);
INSERT INTO prioridades (nome, descricao, peso, status) VALUES ('Gestante', 'Atendimento prioritáro para gestantes', 1, 1);
INSERT INTO prioridades (nome, descricao, peso, status) VALUES ('Idoso', 'Atendimento prioritáro para idosos', 1, 1);
INSERT INTO prioridades (nome, descricao, peso, status) VALUES ('Outros', 'Qualquer outra prioridade', 1, 1);

-- locais de atendimento
INSERT INTO locais (nome) VALUES ('Guichê');
INSERT INTO locais (nome) VALUES ('Sala');
INSERT INTO locais (nome) VALUES ('Mesa');

-- cargo raiz (admin)
INSERT INTO cargos (nome, descricao, esquerda, direita, nivel) VALUES ('Administrador', 'Administrador geral do sistema', 1, 2, 0);
-- cargo vs modulo
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) SELECT (SELECT id FROM cargos), id, 3 FROM modulos;

-- grupo raiz
INSERT INTO grupos (nome, descricao, esquerda, direita, nivel) VALUES ('Raíz', 'Grupo Raíz', 1, 2, 0);

-- unidade padrao
INSERT INTO unidades (grupo_id, codigo, nome, status, stat_imp, msg_imp) VALUES ((SELECT id FROM grupos), '1', 'Unidade Padrão', 1, 1, 'Novo SGA');

-- usuario administrador
INSERT INTO usuarios (login, nome, sobrenome, senha, ult_acesso, status, session_id) VALUES ('{login}', '{nome}', '{sobrenome}', '{senha}', NULL, 1, '');

INSERT INTO usu_grup_cargo (usuario_id, grupo_id, cargo_id) SELECT id, (SELECT id FROM grupos), (SELECT id FROM cargos) FROM usuarios;
