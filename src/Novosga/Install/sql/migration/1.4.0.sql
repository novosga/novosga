
ALTER TABLE uni_serv DROP COLUMN nome;

CREATE TABLE uni_meta (
    unidade_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT ,
    PRIMARY KEY (unidade_id, name),
    FOREIGN KEY (unidade_id) REFERENCES unidades (id)
);