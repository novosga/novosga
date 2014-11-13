
ALTER TABLE historico_atendimentos ADD COLUMN atendimento_id bigint;
ALTER TABLE historico_atendimentos ADD FOREIGN KEY (atendimento_id) REFERENCES historico_atendimentos (id);

CREATE TABLE contador (
    unidade_id INT NOT NULL,
    total INT NOT NULL DEFAULT 0,
    PRIMARY KEY(unidade_id)
);

ALTER TABLE contador ADD FOREIGN KEY (unidade_id) REFERENCES unidades (id);