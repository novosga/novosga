
-- modulos globais
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (1, 'sga.locais', 'Locais', 'Gerencie os locais de atendimento', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (2, 'sga.prioridades', 'Prioridades', 'Gerencie os prioridades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (3, 'sga.servicos', 'Serviços', 'Gerencie os serviços do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (4, 'sga.grupos', 'Grupos', 'Gerencie os grupos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (5, 'sga.unidades', 'Unidades', 'Gerencie as unidades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (6, 'sga.cargos', 'Cargos', 'Gerencie os cargos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (7, 'sga.usuarios', 'Usuários', 'Gerencie os usuários do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (8, 'sga.modulos', 'Módulos', 'Gerencie os módulos instalados', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (9, 'sga.admin', 'Administração', 'Configurações gerais do sistema', 'rogeriolino', 1, 1);
-- modulos locais
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (10, 'sga.triagem', 'Triagem', 'Gerencie a distribuíção das senhas da unidade atual', 'rogeriolino', 0, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (11, 'sga.monitor', 'Monitor', 'Gerencie as senhas aguardando atendimento', 'rogeriolino', 0, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (12, 'sga.atendimento', 'Atendimento', 'Efetue o atendimento às senhas distribuídas dos serviços que você atende', 'rogeriolino', 0, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (13, 'sga.estatisticas', 'Estatísticas', 'Visualize e exporte estastísticas e relatórios sobre o sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave, nome, descricao, autor, tipo, status) VALUES (14, 'sga.unidade', 'Configuração', 'Módulo para gerenciamento da unidade atual', 'rogeriolino', 0, 1);

-- prioridades
INSERT INTO prioridades (id, nome, descricao, peso, status) VALUES (1, 'Sem prioridade', 'Atendimento normal', 0, 1);
INSERT INTO prioridades (id, nome, descricao, peso, status) VALUES (2, 'Portador de Deficiência', 'Atendimento prioritáro para portadores de deficiência', 1, 1);
INSERT INTO prioridades (id, nome, descricao, peso, status) VALUES (3, 'Gestante', 'Atendimento prioritáro para gestantes', 1, 1);
INSERT INTO prioridades (id, nome, descricao, peso, status) VALUES (4, 'Idoso', 'Atendimento prioritáro para idosos', 1, 1);
INSERT INTO prioridades (id, nome, descricao, peso, status) VALUES (5, 'Outros', 'Qualquer outra prioridade', 1, 1);

INSERT INTO locais (id, nome) VALUES (1, 'Guichê');
INSERT INTO locais (id, nome) VALUES (2, 'Sala');
INSERT INTO locais (id, nome) VALUES (3, 'Mesa');

-- cargo raiz (admin)
INSERT INTO cargos (id, nome, descricao, esquerda, direita, nivel) VALUES (1, 'Administrador', 'Administrador geral do sistema', 1, 2, 0);
-- cargo vs modulo
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 1, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 2, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 3, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 4, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 5, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 6, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 7, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 8, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 9, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 10, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 11, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 12, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 13, 3);
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) VALUES (1, 14, 3);

-- grupo raiz
INSERT INTO grupos (id, nome, descricao, esquerda, direita, nivel) VALUES (1, 'Raíz', 'Grupo Raíz', 1, 2, 0);

-- unidade padrao
INSERT INTO unidades (id, grupo_id, codigo, nome, status, stat_imp, msg_imp) VALUES (1, 1, '1', 'Unidade Padrão', 1, 0, 'Novo SGA');

-- administrador
INSERT INTO usuarios (id, login, nome, sobrenome, senha, ult_acesso, status, session_id) VALUES (1, '{login}', '{nome}', '{sobrenome}', '{senha}', NULL, 1, '');
INSERT INTO usu_grup_cargo (usuario_id, grupo_id, cargo_id) VALUES (1, 1, 1);
