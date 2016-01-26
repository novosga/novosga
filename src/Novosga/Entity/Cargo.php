<?php

namespace Novosga\Entity;

/**
 * Classe Cargo
 * Um cargo define permissões de acesso a módulos do sistema.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Cargo extends TreeModel
{
    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string
     */
    protected $descricao;

    /**
     * @var Permissao[]
     */
    protected $permissoes;

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

    /**
     * Adicinoa permissão para acessar módulo.
     *
     * @param $pm
     *
     * @return none
     */
    public function addPermissao(Permissao $pm)
    {
        $this->permissoes[] = $pm;
    }

    /**
     * Retorna as permissões do cargo.
     *
     * @return $permissoes array
     */
    public function getPermissoes()
    {
        return $this->permissoes;
    }

    /**
     * Verfica se tem permissão para acessar módulo.
     *
     * @param $modulo
     *
     * @return bool
     */
    public function hasPermissao(Modulo $modulo)
    {
        foreach ($this->getPermissoes() as $permissao) {
            if ($permissao->getModulo()->getId() == $modulo->getId()) {
                return true;
            }
        }

        return false;
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
