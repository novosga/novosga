<?php

namespace App\Entity;

use App\Repository\PainelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\PainelInterface;
use Novosga\Entity\PainelServicoInterface;
use Novosga\Entity\UnidadeInterface;

#[ORM\Table(name: 'paineis')]
#[ORM\Entity(repositoryClass: PainelRepository::class)]
class Painel implements PainelInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "lotacoes_id_seq", allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?UnidadeInterface $unidade = null;

    #[ORM\Column(length: 255)]
    private ?string $nome = null;

    /** @var Collection<int, PainelServicoInterface> */
    #[ORM\OneToMany(targetEntity: PainelServico::class, cascade: ['persist'], mappedBy: 'painel', orphanRemoval: true)]
    private Collection $servicos;

    #[ORM\Column(length: 36)]
    private ?string $publicId = null;

    public function __construct()
    {
        $this->servicos = new ArrayCollection();
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

    public function getUnidade(): ?UnidadeInterface
    {
        return $this->unidade;
    }

    public function setUnidade(?UnidadeInterface $unidade): static
    {
        $this->unidade = $unidade;

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

    /**
     * @return Collection<int, PainelServicoInterface>
     */
    public function getServicos(): Collection
    {
        return $this->servicos;
    }

    public function addServico(PainelServicoInterface $servico): static
    {
        if (!$this->servicos->contains($servico)) {
            $this->servicos->add($servico);
            $servico->setPainel($this);
        }

        return $this;
    }

    public function removeServico(PainelServicoInterface $servico): static
    {
        if ($this->servicos->removeElement($servico)) {
            // set the owning side to null (unless already changed)
            if ($servico->getPainel() === $this) {
                $servico->setPainel(null);
            }
        }

        return $this;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function setPublicId(?string $publicId): static
    {
        $this->publicId = $publicId;

        return $this;
    }
}
