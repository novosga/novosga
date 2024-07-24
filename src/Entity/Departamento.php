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
use App\Repository\DepartamentoRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\DepartamentoInterface;

/**
 * Departamento
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: DepartamentoRepository::class)]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
])]
#[ORM\Table(name: 'departamentos')]
class Departamento implements TimestampableEntityInterface, DepartamentoInterface
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "departamentos_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $nome = null;

    #[ORM\Column(length: 250)]
    private ?string $descricao;

    #[ORM\Column]
    private bool $ativo;

    public function __construct()
    {
        $this->ativo = true;
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

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setDescricao(?string $descricao): static
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
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
            'ativo'     => $this->isAtivo(),
        ];
    }
}
