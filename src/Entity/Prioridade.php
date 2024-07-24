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

use App\EventListener\PrioridadeListener;
use App\EventListener\TimestampableEntityListener;
use App\Repository\PrioridadeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\PrioridadeInterface;

/**
 * Prioridade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: PrioridadeRepository::class)]
#[ORM\Table(name: 'prioridades')]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
    PrioridadeListener::class,
])]
class Prioridade implements TimestampableEntityInterface, SoftDeletableEntityInterface, PrioridadeInterface
{
    use TimestampableEntityTrait;
    use SoftDeletableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "prioridades_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $nome = null;

    #[ORM\Column(length: 100)]
    private ?string $descricao;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $peso = null;

    #[ORM\Column(length: 20)]
    private ?string $cor;

    #[ORM\Column]
    private bool $ativo = true;

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

    public function getPeso(): ?int
    {
        return $this->peso;
    }

    public function setPeso(?int $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): static
    {
        $this->ativo = $ativo;

        return $this;
    }

    public function getCor(): ?string
    {
        return $this->cor;
    }

    public function setCor(?string $cor): static
    {
        $this->cor = $cor;

        return $this;
    }

    public function __toString()
    {
        return $this->getNome();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'peso'      => $this->getPeso(),
            'cor'       => $this->getCor(),
            'ativo'     => $this->isAtivo(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
            'deletedAt' => $this->getDeletedAt() ? $this->getDeletedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
