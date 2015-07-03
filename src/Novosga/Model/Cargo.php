<?php

namespace Novosga\Model;

/**
 * Classe Cargo
 * Um cargo define permissões de acesso a módulos do sistema.
 *
 * @Entity
 * @Table(name="cargos")
 */
class Cargo extends TreeModel
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
     * @OneToMany(targetEntity="Permissao", mappedBy="cargo")
     */
    protected $permissoes;

    /**
     * Define o nome do Cargo.
     *
     * @param String $nome
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
     * @param String $nome
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * Retorna o nome do Cargo.
     *
     * @return String
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
