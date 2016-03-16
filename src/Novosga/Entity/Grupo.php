<?php

namespace Novosga\Entity;

/**
 * Classe Grupo
 * Atraves do grupo e definido o acesso do Usuario.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Grupo extends TreeModel
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
     * @var Unidade
     */
    private $unidade;

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
        $arr = parent::jsonSerialize();

        return array_merge($arr, [
            'nome'      => $this->getNome(),
            'descricao' => $this->getDescricao(),
        ]);
    }
}
