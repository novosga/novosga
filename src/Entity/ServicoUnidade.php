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

use App\Repository\ServicoUnidadeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\DepartamentoInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUnidadeInterface;
use Novosga\Entity\UnidadeInterface;

/**
 * Servico Unidade
 * Configuração do serviço na unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: ServicoUnidadeRepository::class)]
#[ORM\Table(name: 'servicos_unidades')]
class ServicoUnidade implements ServicoUnidadeInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Servico::class, inversedBy: 'servicosUnidade')]
    private ?ServicoInterface $servico = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    private ?UnidadeInterface $unidade = null;

    #[ORM\ManyToOne(targetEntity: Departamento::class)]
    private ?DepartamentoInterface $departamento = null;

    #[ORM\Column(length: 3)]
    private ?string $sigla = null;

    #[ORM\Column]
    private bool $ativo = true;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $peso = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $tipo = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $incremento = null;

    #[ORM\Column]
    private ?int $numeroInicial = null;

    #[ORM\Column(nullable: true)]
    private ?int $numeroFinal = null;

    #[ORM\Column(nullable: true)]
    private ?int $maximo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mensagem;

    public function __construct()
    {
        $this->tipo = self::ATENDIMENTO_TODOS;
        $this->numeroInicial = 1;
        $this->incremento = 1;
        $this->peso = 1;
        $this->sigla = '';
        $this->mensagem = '';
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

    public function getUnidade(): ?UnidadeInterface
    {
        return $this->unidade;
    }

    public function setUnidade(?UnidadeInterface $unidade): static
    {
        $this->unidade = $unidade;

        return $this;
    }

    public function getDepartamento(): ?DepartamentoInterface
    {
        return $this->departamento;
    }

    public function setDepartamento(?DepartamentoInterface $departamento): static
    {
        $this->departamento = $departamento;

        return $this;
    }

    public function setAtivo(bool $ativo): static
    {
        $this->ativo = !!$ativo;

        return $this;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
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

    public function getSigla(): string
    {
        return $this->sigla;
    }

    public function setSigla(string $sigla): static
    {
        $this->sigla = $sigla;

        return $this;
    }

    public function getTipo(): ?int
    {
        return $this->tipo;
    }

    public function setTipo(?int $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getIncremento(): ?int
    {
        return $this->incremento;
    }

    public function setIncremento(?int $incremento): static
    {
        $this->incremento = $incremento;

        return $this;
    }

    public function getNumeroInicial(): ?int
    {
        return $this->numeroInicial;
    }

    public function setNumeroInicial(?int $numeroInicial): static
    {
        $this->numeroInicial = $numeroInicial;

        return $this;
    }

    public function getNumeroFinal(): ?int
    {
        return $this->numeroFinal;
    }

    public function setNumeroFinal(?int $numeroFinal): static
    {
        $this->numeroFinal = $numeroFinal;

        return $this;
    }

    public function getMaximo(): ?int
    {
        return $this->maximo;
    }

    public function setMaximo(?int $maximo): static
    {
        $this->maximo = $maximo;

        return $this;
    }

    public function getMensagem(): ?string
    {
        return $this->mensagem;
    }

    public function setMensagem(?string $mensagem): static
    {
        $this->mensagem = $mensagem;

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s-%s', $this->sigla, $this->getServico()->getNome());
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'sigla' => $this->getSigla(),
            'peso' => $this->getPeso(),
            'servico' => $this->getServico(),
            'departamento' => $this->getDepartamento(),
            'ativo' => $this->isAtivo(),
            'tipo' => $this->getTipo(),
            'mensagem' => $this->getMensagem(),
            'numeroInicial' => $this->getNumeroInicial(),
            'numeroFinal' => $this->getNumeroFinal(),
            'incremento' => $this->getIncremento(),
            'maximo' => $this->getMaximo(),
        ];
    }
}
