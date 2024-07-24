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

use App\Repository\ContadorRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\ContadorInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;

/**
 * Ticket counter.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: ContadorRepository::class)]
#[ORM\Table(name: 'contador')]
class Contador implements ContadorInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?UnidadeInterface $unidade;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Servico::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServicoInterface $servico;

    #[ORM\Column(nullable: true)]
    private ?int $numero;

    public function getUnidade(): ?UnidadeInterface
    {
        return $this->unidade;
    }

    public function setUnidade(?UnidadeInterface $unidade): static
    {
        $this->unidade = $unidade;

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

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'numero'  => $this->getNumero(),
            'servico' => $this->getServico()
        ];
    }
}
