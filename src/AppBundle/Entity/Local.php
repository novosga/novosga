<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ ORM\Entity(repositoryClass="Novosga\Repository\LocalRepository")
 * @ ORM\Table(name="locais")
 * @UniqueEntity("nome")
 */
class Local extends SequencialModel
{
    /**
     * @ ORM\Column(type="string", name="nome", length=20, nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
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
        return [
            'id'   => $this->getId(),
            'nome' => $this->getNome(),
        ];
    }
}
