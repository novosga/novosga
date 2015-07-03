<?php

namespace Novosga\Model;

/**
 * @Entity
 * @Table(name="locais")
 */
class Local extends SequencialModel
{
    /**
     * @Column(type="string", name="nome", length=20, nullable=false, unique=true)
     */
    protected $nome;

    public function __construct()
    {
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'nome' => $this->getNome(),
        );
    }
}
