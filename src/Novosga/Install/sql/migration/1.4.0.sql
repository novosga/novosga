
ALTER TABLE uni_serv DROP COLUMN nome;

CREATE TABLE uni_meta (
    unidade_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT ,
    PRIMARY KEY (unidade_id, name),
    FOREIGN KEY (unidade_id) REFERENCES unidades (id)
);

CREATE TABLE serv_meta (
    servico_id integer NOT NULL,
    name varchar(50) NOT NULL,
    value TEXT ,
    PRIMARY KEY (servico_id, name),
    FOREIGN KEY (servico_id) REFERENCES servicos (id)
);