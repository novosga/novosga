
-- postgres migration script
-- @author rogeriolino

-- unidades
ALTER TABLE unidades DROP COLUMN id_tema;
ALTER TABLE unidades ADD COLUMN stat_imp smallint DEFAULT 0;
ALTER TABLE unidades ADD COLUMN msg_imp varchar(100);
UPDATE unidades u SET u.stat_imp = s.stat_imp, u.msg_imp = s.msg_imp FROM senha_uni_msg s WHERE s.id_uni = u.id_uni;

-- modulos
ALTER TABLE modulos DROP COLUMN img_mod;

-- usuarios
ALTER TABLE usuarios ADD COLUMN session_id varchar(40) NOT NULL;

DROP TABLE menus;
DROP TABLE senha_uni_msg;
DROP TABLE temas;
DROP TABLE usu_session;
DROP TABLE variaveis_sistema;