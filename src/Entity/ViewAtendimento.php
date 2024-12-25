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

use App\Repository\ViewAtendimentoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Novosga\Entity\AtendimentoCodificadoInterface;
use Novosga\Entity\AtendimentoInterface;

/**
 * View Atendimento
 * União dos atendimentos atuais e do histórico
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(readOnly: true, repositoryClass: ViewAtendimentoRepository::class)]
#[ORM\Table(name: 'view_atendimentos')]
class ViewAtendimento extends AbstractAtendimento
{
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ViewAtendimento::class)]
    #[ORM\JoinColumn(name: 'atendimento_id')]
    protected ?AtendimentoInterface $pai = null;

    /** @var Collection<int,AtendimentoCodificadoInterface> */
    #[ORM\OneToMany(targetEntity: ViewAtendimentoCodificado::class, mappedBy: 'atendimento')]
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

    public function addCodificado(ViewAtendimentoCodificado $codificado): static
    {
        if (!$this->codificados->contains($codificado)) {
            $this->codificados->add($codificado);
            $codificado->setAtendimento($this);
        }

        return $this;
    }

    public function removeCodificado(ViewAtendimentoCodificado $codificado): static
    {
        if ($this->codificados->removeElement($codificado)) {
            // set the owning side to null (unless already changed)
            if ($codificado->getAtendimento() === $this) {
                $codificado->setAtendimento(null);
            }
        }

        return $this;
    }
}
