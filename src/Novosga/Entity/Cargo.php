<?php

namespace Novosga\Entity;

/**
 * Classe Cargo
 * Um cargo define permissões de acesso a módulos do sistema.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Cargo extends SequencialModel
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
     * @var Modulo[]
     */
    private $modulos;

    /**
     * Define o nome do Cargo.
     *
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * Retorna a descrição do Cargo.
     *
     * @return int
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Define a descrição do Cargo.
     *
     * @param string $nome
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * Retorna o nome do Cargo.
     *
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    public function getModulos()
    {
        return $this->modulos;
    }

    public function setModulos($modulos)
    {
        $this->modulos = $modulos;
        return $this;
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
