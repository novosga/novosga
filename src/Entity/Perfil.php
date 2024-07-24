<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\EventListener\TimestampableEntityListener;
use App\Repository\PerfilRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\PerfilInterface;

/**
 * Classe Perfil
 * O perfil define permissões de acesso aos módulos do sistema.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: PerfilRepository::class)]
#[ORM\Table(name: 'perfis')]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
])]
class Perfil implements TimestampableEntityInterface, PerfilInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "perfis_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nome = null;

    #[ORM\Column(length: 150)]
    private ?string $descricao;

    /** @var string[] */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $modulos = [];

    public function __construct()
    {
        $this->modulos = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): static
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getModulos(): array
    {
        return $this->modulos;
    }

    public function setModulos(array $modulos): static
    {
        $this->modulos = $modulos;

        return $this;
    }

    public function __toString()
    {
        return $this->nome;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'modulos'   => $this->getModulos(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
