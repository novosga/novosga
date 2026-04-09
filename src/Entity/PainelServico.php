<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PainelServicoRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\PainelInterface;
use Novosga\Entity\PainelServicoInterface;
use Novosga\Entity\ServicoInterface;

#[ORM\Table(name: 'painel_servicos')]
#[ORM\Entity(repositoryClass: PainelServicoRepository::class)]
class PainelServico implements PainelServicoInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "lotacoes_id_seq", allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Painel::class, inversedBy: 'servicos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PainelInterface $painel = null;

    #[ORM\ManyToOne(targetEntity: Servico::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServicoInterface $servico = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPainel(): ?PainelInterface
    {
        return $this->painel;
    }

    public function setPainel(?PainelInterface $painel): static
    {
        $this->painel = $painel;

        return $this;
    }

    public function getServico(): ?ServicoInterface
    {
        return $this->servico;
    }

    public function setServico(?ServicoInterface $servico): static
    {
        $this->servico = $servico;

        return $this;
    }
}
