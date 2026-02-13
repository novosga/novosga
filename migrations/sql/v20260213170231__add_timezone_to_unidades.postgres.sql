DO $body$
BEGIN

ALTER TABLE unidades ADD timezone VARCHAR(50) DEFAULT NULL;

END $body$;
