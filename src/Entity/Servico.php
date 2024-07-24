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

use App\EventListener\ServicoListener;
use App\EventListener\TimestampableEntityListener;
use App\Repository\ServicoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUnidadeInterface;

/**
 * Servico
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: ServicoRepository::class)]
#[ORM\EntityListeners([
    TimestampableEntityListener::class,
    ServicoListener::class,
])]
#[ORM\Table(name: 'servicos')]
class Servico implements TimestampableEntityInterface, SoftDeletableEntityInterface, ServicoInterface
{
    use TimestampableEntityTrait;
    use SoftDeletableEntityTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "servicos_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nome = null;

    #[ORM\Column(length: 250)]
    private ?string $descricao;

    #[ORM\Column]
    private bool $ativo = true;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $peso = null;

    #[ORM\ManyToOne(targetEntity: Servico::class, inversedBy: 'subServicos')]
    #[ORM\JoinColumn(name: 'macro_id')]
    private ?ServicoInterface $mestre = null;

    /** @var Collection<int,ServicoInterface> */
    #[ORM\OneToMany(targetEntity: Servico::class, mappedBy: 'mestre')]
    private Collection $subServicos;

    /** @var Collection<int,ServicoUnidadeInterface> */
    #[ORM\OneToMany(targetEntity: ServicoUnidade::class, mappedBy: 'servico')]
    private Collection $servicosUnidade;

    public function __construct()
    {
        $this->subServicos = new ArrayCollection();
        $this->servicosUnidade = new ArrayCollection();
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

    public function getMestre(): ?ServicoInterface
    {
        return $this->mestre;
    }

    public function setMestre(?ServicoInterface $servico): static
    {
        $this->mestre = $servico;

        return $this;
    }

    public function isMestre(): bool
    {
        return ($this->getId() && !$this->getMestre());
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

    public function getPeso(): ?int
    {
        return $this->peso;
    }

    public function setPeso(?int $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getSubServicos(): Collection
    {
        return $this->subServicos;
    }

    public function setSubServicos(Collection $subServicos): static
    {
        $this->subServicos = $subServicos;

        return $this;
    }

    public function getServicosUnidade(): Collection
    {
        return $this->servicosUnidade;
    }

    public function setServicosUnidade(Collection $servicosUnidade): static
    {
        $this->servicosUnidade = $servicosUnidade;

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
            'peso'      => $this->getPeso(),
            'ativo'     => $this->isAtivo(),
            'macro'     => $this->getMestre(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d\TH:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d\TH:i:s') : null,
            'deletedAt' => $this->getDeletedAt() ? $this->getDeletedAt()->format('Y-m-d\TH:i:s') : null,
        ];
    }
}
