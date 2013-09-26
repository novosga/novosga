
-- modulos globais
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (1, 'sga.prioridades', 'Prioridades', 'Gerencie os prioridades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (2, 'sga.servicos', 'Serviços', 'Gerencie os serviços do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (3, 'sga.grupos', 'Grupos', 'Gerencie os grupos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (4, 'sga.unidades', 'Unidades', 'Gerencie as unidades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (5, 'sga.cargos', 'Cargos', 'Gerencie os cargos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (6, 'sga.usuarios', 'Usuários', 'Gerencie os usuários do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (7, 'sga.modulos', 'Módulos', 'Gerencie os módulos instalados', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (8, 'sga.admin', 'Administração', 'Configurações gerais do sistema', 'rogeriolino', 1, 1);
-- modulos locais
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (9, 'sga.triagem', 'Triagem', 'Gerencie a distribuíção das senhas da unidade atual', 'rogeriolino', 0, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (10, 'sga.monitor', 'Monitor', 'Gerencie as senhas aguardando atendimento', 'rogeriolino', 0, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (11, 'sga.atendimento', 'Atendimento', 'Efetue o atendimento às senhas distribuídas dos serviços que você atende', 'rogeriolino', 0, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (12, 'sga.estatisticas', 'Estatísticas', 'Visualize e exporte estastísticas e relatórios sobre o sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (id, chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES (13, 'sga.unidade', 'Configuração', 'Módulo para gerenciamento da unidade atual', 'rogeriolino', 0, 1);

-- prioridades
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (1, 'Sem prioridade', 'Atendimento normal', 0, 1);
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (2, 'Deficiente Auditivo', 'Atendimento prioritáro para Deficiente Auditivo', 1, 1);
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (3, 'Deficiente Físico', 'Atendimento prioritáro para Deficiente Físico', 1, 1);
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (4, 'Deficiente Visual', 'Atendimento prioritáro para Deficiente Visual', 1, 1);
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (5, 'Gestante', 'Atendimento prioritáro para Gestante', 1, 1);
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (6, 'Idoso', 'Atendimento prioritáro para Idoso', 1, 1);
INSERT INTO prioridades (id, nm_pri, desc_pri, peso_pri, stat_pri) VALUES (7, 'Outros', 'Qualquer outra prioridade', 1, 1);

INSERT INTO locais (id, nm_loc) VALUES (1, 'Guichê');

-- cargo raiz (admin)
INSERT INTO cargos (nm_cargo, desc_cargo, esquerda, direita) VALUES ('Administrador', 'Administrador geral do sistema', 1, 2);
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

-- grupo raiz
INSERT INTO grupos (nm_grupo, desc_grupo, esquerda, direita) VALUES ('Raíz', 'Grupo Raíz', 1, 2);

-- unidade padrao
INSERT INTO unidades (grupo_id, cod_uni, nm_uni, stat_uni, stat_imp, msg_imp) VALUES (1, '1', 'Unidade Padrão', 1, 0, 'Novo SGA');

-- administrador
INSERT INTO usuarios (login_usu, nm_usu, ult_nm_usu, senha_usu, ult_acesso, stat_usu, session_id) VALUES ('{login_usu}', '{nm_usu}', '{ult_nm_usu}', '{senha_usu}', NULL, 1, '');
INSERT INTO usu_grup_cargo (usuario_id, grupo_id, cargo_id) VALUES (1, 1, 1);
