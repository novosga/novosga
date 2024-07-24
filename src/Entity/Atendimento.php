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

use App\Repository\AtendimentoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Novosga\Entity\AtendimentoCodificadoInterface;
use Novosga\Entity\AtendimentoInterface;

/**
 * Classe Atendimento
 * contem o Cliente, o Servico e o Status do atendimento.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: AtendimentoRepository::class)]
#[ORM\Table(name: 'atendimentos')]
class Atendimento extends AbstractAtendimento
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "atendimentos_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Atendimento::class)]
    #[ORM\JoinColumn(name: 'atendimento_id')]
    protected ?AtendimentoInterface $pai = null;

    /** @var Collection<int,AtendimentoCodificadoInterface> */
    #[ORM\OneToMany(targetEntity: AtendimentoCodificado::class, mappedBy: 'atendimento')]
    private Collection $codificados;

    public function __construct()
    {
        parent::__construct();
        $this->codificados = new ArrayCollection();
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

    public function getPai(): ?AtendimentoInterface
    {
        return $this->pai;
    }

    public function setPai(?AtendimentoInterface $pai): static
    {
        $this->pai = $pai;

        return $this;
    }

    public function getCodificados(): Collection
    {
        return $this->codificados;
    }

    public function setCodificados(Collection $codificados): static
    {
        $this->codificados = $codificados;

        return $this;
    }

    public function hash(): string
    {
        return sha1("{$this->getId()}:{$this->getDataChegada()->getTimestamp()}");
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'hash' => $this->hash(),
        ]);
    }
}
