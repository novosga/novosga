<?php

namespace Novosga\Entity;

/**
 * Local de atendimento
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Local extends SequencialModel
{
    /**
     * @var string
     */
    private $nome;

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
        return [
            'id'   => $this->getId(),
            'nome' => $this->getNome(),
        ];
    }
}
