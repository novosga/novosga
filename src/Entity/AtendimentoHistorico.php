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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Novosga\Entity\AtendimentoCodificadoInterface;
use Novosga\Entity\AtendimentoInterface;

/**
 * AtendimentoHistorico
 * historico de atendimento do banco de dados.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'historico_atendimentos')]
class AtendimentoHistorico extends AbstractAtendimento
{
    #[ORM\Id]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AtendimentoHistorico::class)]
    #[ORM\JoinColumn(name: 'atendimento_id')]
    protected ?AtendimentoInterface $pai = null;

    /** @var Collection<int,AtendimentoCodificadoInterface> */
    #[ORM\OneToMany(targetEntity: AtendimentoCodificadoHistorico::class, mappedBy: 'atendimento')]
    private Collection $codificados;

    public function __construct()
    {
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
}
