
-- cargo vs modulo
INSERT INTO cargos_mod_perm (cargo_id, modulo_id, permissao) SELECT (SELECT id FROM cargos), id, 3 FROM modulos;

-- usuario administrador
INSERT INTO usuarios (login, nome, sobrenome, senha, ult_acesso, status, session_id) VALUES ('{login}', '{nome}', '{sobrenome}', '{senha}', NULL, 1, '');

-- usuario vs grupo vs cargo
INSERT INTO usu_grup_cargo (usuario_id, grupo_id, cargo_id) SELECT id, (SELECT id FROM grupos), (SELECT id FROM cargos) FROM usuarios;
