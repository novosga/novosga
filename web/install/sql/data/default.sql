
-- status
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Passou pela Triagem', 'Cliente chegou na unidade e foi atendido pela Triagem');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Chamado pela mesa', 'Atendente chamou o proximo da fila');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Atendimento Iniciado', 'Cliente chegou no guiche e esta sendo atendido');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Atendimento Encerrado', 'Atendente encerrou o atendimento, mas ainda não codificou os serviços atendidos.');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Nao Compareceu', 'Apos chamar o proximo da fila, o mesmo nao compareceu');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Cancelada', 'Senha Cancelada por algum motivo');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Erro de triagem', 'Responsavel pela Triagem emitiu a senha para atendimento errado');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Atendimento Codificado', 'Atendimento finalizado.');
INSERT INTO atend_status (nm_stat, desc_stat) VALUES ('Agendamento não confirmado', '');

-- modulos globais
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.admin', 'Administração', 'Configurações gerais do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.prioridades', 'Prioridades', 'Gerencie os prioridades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.servicos', 'Serviços', 'Gerencie os serviços do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.grupos', 'Grupos', 'Gerencie os grupos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.unidades', 'Unidades', 'Gerencie as unidades do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.cargos', 'Cargos', 'Gerencie os cargos do sistema', 'rogeriolino', 1, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.usuarios', 'Usuários', 'Gerencie os usuários do sistema', 'rogeriolino', 1, 1);
-- modulos locais
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.unidade', 'Configuração', 'Módulo para gerenciamento da unidade atual', 'rogeriolino', 0, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.triagem', 'Triagem', 'Gerencie a distribuíção das senhas da unidade atual', 'rogeriolino', 0, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.monitor', 'Monitor', 'Gerencie as senhas aguardando atendimento', 'rogeriolino', 0, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.atendimento', 'Atendimento', 'Efetue o atendimento às senhas distribuídas dos serviços que você atende', 'rogeriolino', 0, 1);
INSERT INTO modulos (chave_mod, nm_mod, desc_mod, autor_mod, tipo_mod, stat_mod) VALUES ('sga.estatisticas', 'Estatísticas', 'Visualize e exporte estastísticas e relatórios sobre o sistema', 'rogeriolino', 1, 1);

-- prioridades
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Sem prioridade', 'Atendimento normal', 0, 1);
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Deficiente Auditivo', 'Atendimento prioritáro para Deficiente Auditivo', 1, 1);
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Deficiente Físico', 'Atendimento prioritáro para Deficiente Físico', 1, 1);
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Deficiente Visual', 'Atendimento prioritáro para Deficiente Visual', 1, 1);
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Gestante', 'Atendimento prioritáro para Gestante', 1, 1);
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Idoso', 'Atendimento prioritáro para Idoso', 1, 1);
INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES ('Outros', 'Qualquer outra prioridade', 1, 1);

INSERT INTO serv_local (nm_loc) VALUES ('Guichê');

-- cargo raiz (admin)
INSERT INTO cargos_aninhados (nm_cargo, desc_cargo, esquerda, direita) VALUES ('Administrador', 'Administrador geral do sistema', 1, 2);
-- cargo vs modulo
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 1, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 2, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 3, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 4, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 5, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 6, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 7, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 8, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 9, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 10, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 11, 3);
INSERT INTO cargos_mod_perm (id_cargo, id_mod, permissao) VALUES (1, 12, 3);

-- grupo raiz
INSERT INTO grupos_aninhados (nm_grupo, desc_grupo, esquerda, direita) VALUES ('Raíz', 'Grupo Raíz', 1, 2);

-- unidade padrao
INSERT INTO unidades (id_grupo, cod_uni, nm_uni, stat_uni, stat_imp, msg_imp) VALUES (1, '1', 'Unidade Padrão', 1, 0, 'Novo SGA');

-- administrador
INSERT INTO usuarios (login_usu, nm_usu, ult_nm_usu, senha_usu, ult_acesso, stat_usu) VALUES ('{login_usu}', '{nm_usu}', '{ult_nm_usu}', '{senha_usu}', NULL, 1);
INSERT INTO usu_grup_cargo (id_usu, id_grupo, id_cargo) VALUES (1, 1, 1);
