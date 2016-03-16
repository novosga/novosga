<?php

namespace Novosga\Entity;

/**
 * Prioridade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Prioridade extends SequencialModel
{
    /**
     * @var string
     */
    private $nome;

    /**
     * @var string
     */
    private $descricao;

    /**
     * @var int
     */
    private $peso;

    /**
     * @var int
     */
    private $status;

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

    public function setDescricao($desc)
    {
        $this->descricao = $desc;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setPeso($peso)
    {
        if (is_int($peso) && $peso >= 0) {
            $this->peso = $peso;
        } else {
            throw new Exception(_('O peso da prioridade deve ser um inteiro positivo'));
        }
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function toString()
    {
        return $this->getNome();
    }

    public function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'peso'      => $this->getPeso(),
        ];
    }
}
