
-- painel_senha

ALTER TABLE painel_senha ADD COLUMN prioridade VARCHAR(100);
ALTER TABLE painel_senha ADD COLUMN nome_cliente VARCHAR(100);
ALTER TABLE painel_senha ADD COLUMN documento_cliente VARCHAR(30);

-- modulo

ALTER TABLE modulos DROP COLUMN autor;