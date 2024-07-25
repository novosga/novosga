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
use App\EventListener\UnidadeListener;
use App\Repository\UnidadeRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\ConfiguracaoImpressaoInterface;
use Novosga\Entity\UnidadeInterface;

/**
 * Unidade de atendimento.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: UnidadeRepository::class)]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
    UnidadeListener::class,
])]
#[ORM\Table(name: 'unidades')]
class Unidade implements TimestampableEntityInterface, SoftDeletableEntityInterface, UnidadeInterface
{
    use TimestampableEntityTrait;
    use SoftDeletableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "unidades_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nome = null;

    #[ORM\Column(length: 250)]
    private ?string $descricao;

    #[ORM\Column]
    private bool $ativo = true;

    #[ORM\Embedded(class: ConfiguracaoImpressao::class)]
    private ConfiguracaoImpressaoInterface $impressao;

    public function __construct()
    {
        $this->impressao = new ConfiguracaoImpressao();
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

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): static
    {
        $this->descricao = $descricao;

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

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): static
    {
        $this->ativo = $ativo;

        return $this;
    }

    public function getImpressao(): ConfiguracaoImpressaoInterface
    {
        return $this->impressao;
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
            'impressao' => $this->getImpressao(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
            'deletedAt' => $this->getDeletedAt() ? $this->getDeletedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
