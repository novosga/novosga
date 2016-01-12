<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe Grupo
 * Atraves do grupo e definido o acesso do Usuario.
 *
 * @ ORM\Entity(repositoryClass="Novosga\Repository\GrupoRepository")
 * @ ORM\Table(name="grupos")
 * @UniqueEntity("nome")
 */
class Grupo extends TreeModel
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
     * @ ORM\OneToOne(targetEntity="Unidade", mappedBy="grupo", fetch="LAZY")
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
