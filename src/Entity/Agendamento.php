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

use App\Repository\AgendamentoRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Entity\ClienteInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;

/**
 * Agendamento.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(repositoryClass: AgendamentoRepository::class)]
#[ORM\Table(name: 'agendamentos')]
#[ORM\Index(name: 'agendamento_oid_index', columns: ['oid'])]
class Agendamento implements AgendamentoInterface
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: "agendamentos_id_seq", allocationSize: 1, initialValue: 1)]
    protected ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $data = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?DateTimeInterface $hora = null;

    #[ORM\Column(length: 20)]
    private ?string $situacao;

    #[ORM\ManyToOne(targetEntity: Cliente::class, cascade: ['persist'])]
    private ?ClienteInterface $cliente = null;

    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    private ?UnidadeInterface $unidade = null;

    #[ORM\ManyToOne(targetEntity: Servico::class)]
    private ?ServicoInterface $servico = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dataConfirmacao = null;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $oid = null;

    public function __construct()
    {
        $this->situacao = self::SITUACAO_AGENDADO;
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

    public function getData(): ?DateTimeInterface
    {
        return $this->data;
    }

    public function setData(?DateTimeInterface $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getHora(): ?DateTimeInterface
    {
        return $this->hora;
    }

    public function setHora(?DateTimeInterface $hora): static
    {
        $this->hora = $hora;

        return $this;
    }

    public function getSituacao(): ?string
    {
        return $this->situacao;
    }

    public function setSituacao(?string $situacao): static
    {
        $this->situacao = $situacao;

        return $this;
    }

    public function getCliente(): ?ClienteInterface
    {
        return $this->cliente;
    }

    public function setCliente(?ClienteInterface $cliente): static
    {
        $this->cliente = $cliente;

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

    public function getServico(): ?ServicoInterface
    {
        return $this->servico;
    }

    public function setServico(?ServicoInterface $servico): static
    {
        $this->servico = $servico;

        return $this;
    }

    public function getDataConfirmacao(): ?DateTimeInterface
    {
        return $this->dataConfirmacao;
    }

    public function setDataConfirmacao(?DateTimeInterface $dataConfirmacao): static
    {
        $this->dataConfirmacao = $dataConfirmacao;

        return $this;
    }

    public function getOid(): ?string
    {
        return $this->oid;
    }

    public function setOid(?string $oid): static
    {
        $this->oid = $oid;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'cliente' => $this->getCliente(),
            'servico' => $this->getServico(),
            'unidade' => $this->getUnidade(),
            'data' => $this->getData() ? $this->getData()->format('Y-m-d') : null,
            'hora' => $this->getHora() ? $this->getHora()->format('H:i') : null,
            'dataConfirmacao' => $this->getDataConfirmacao(),
            'oid' => $this->getOid(),
        ];
    }
}
