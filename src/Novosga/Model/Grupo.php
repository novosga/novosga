<?php

namespace Novosga\Model;

/**
 * Classe Grupo
 * Atraves do grupo e definido o acesso do Usuario.
 *
 * @Entity
 * @Table(name="grupos")
 */
class Grupo extends TreeModel
{
    /**
     * @Column(type="string", name="nome", length=50, nullable=false)
     */
    protected $nome;

    /**
     * @Column(type="string", name="descricao", length=150, nullable=false)
     */
    protected $descricao;

    /**
     * @OneToOne(targetEntity="Unidade", mappedBy="grupo", fetch="LAZY")
     */
    protected $unidade;

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    public function getUnidade()
    {
        return $this->unidade;
    }

    public function setUnidade($unidade)
    {
        $this->unidade = $unidade;
    }

    public function toString()
    {
        return $this->nome;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'left' => $this->getLeft(),
            'right' => $this->getRight(),
            'level' => $this->getLevel(),
        );
    }
}
