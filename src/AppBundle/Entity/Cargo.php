<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe Cargo
 * Um cargo define permissões de acesso a módulos do sistema.
 *
 * @ ORM\Entity(repositoryClass="Novosga\Repository\CargoRepository")
 * @ ORM\Table(name="cargos")
 * @UniqueEntity("nome")
 */
class Cargo extends TreeModel
{
    /**
     * @ ORM\Column(type="string", name="nome", length=50, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(max=50)
     */
    protected $nome;

    /**
     * @ ORM\Column(type="string", name="descricao", length=150, nullable=false)
     * @Assert\NotNull()
     * @Assert\Length(max=150)
     */
    protected $descricao;

    /**
     * @ ORM\OneToMany(targetEntity="Permissao", mappedBy="cargo")
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
        return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'left'      => $this->getLeft(),
            'right'     => $this->getRight(),
            'level'     => $this->getLevel(),
        ];
    }
}
