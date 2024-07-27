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

use DateInterval;
use DateTimeInterface;
use App\Entity\Cliente;
use App\Entity\Senha;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\ClienteInterface;
use Novosga\Entity\LocalInterface;
use Novosga\Entity\PrioridadeInterface;
use Novosga\Entity\SenhaInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;

/**
 * AbstractAtendimento.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\MappedSuperclass]
abstract class AbstractAtendimento implements AtendimentoInterface
{
    #[ORM\ManyToOne(targetEntity: Unidade::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected ?UnidadeInterface $unidade = null;

    #[ORM\ManyToOne(targetEntity: Servico::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected ?ServicoInterface $servico = null;

    #[ORM\ManyToOne(targetEntity: Prioridade::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?PrioridadeInterface $prioridade = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    protected ?UsuarioInterface $usuario = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_tri_id', nullable: false)]
    protected ?UsuarioInterface $usuarioTriagem = null;

    #[ORM\ManyToOne(targetEntity: Local::class)]
    protected ?LocalInterface $local = null;

    #[ORM\Column(name: 'num_local', nullable: true)]
    protected ?int $numeroLocal = null;

    #[ORM\Column(name: 'dt_age', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dataAgendamento = null;

    #[ORM\Column(name: 'dt_cheg', type: Types::DATETIME_MUTABLE)]
    protected ?DateTimeInterface $dataChegada = null;

    #[ORM\Column(name: 'dt_cha', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dataChamada = null;

    #[ORM\Column(name: 'dt_ini', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dataInicio = null;

    #[ORM\Column(name: 'dt_fim', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dataFim = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempoEspera = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempoPermanencia = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempoAtendimento = null;

    #[ORM\Column(nullable: true)]
    private ?int $tempoDeslocamento = null;

    #[ORM\Column(length: 25)]
    protected ?string $status = null;

    #[ORM\Column(length: 25, nullable: true)]
    protected ?string $resolucao = null;

    #[ORM\ManyToOne(targetEntity: Cliente::class, cascade: ['persist'])]
    protected ?ClienteInterface $cliente = null;

    #[ORM\Embedded(class: Senha::class, columnPrefix: 'senha_')]
    protected SenhaInterface $senha;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $observacao = null;

    public function __construct()
    {
        $this->senha = new Senha();
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

    public function getUsuario(): ?UsuarioInterface
    {
        return $this->usuario;
    }

    public function setUsuario(?UsuarioInterface $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function setUsuarioTriagem(?UsuarioInterface $usuario): static
    {
        $this->usuarioTriagem = $usuario;

        return $this;
    }

    public function getUsuarioTriagem(): ?UsuarioInterface
    {
        return $this->usuarioTriagem;
    }

    public function getLocal(): ?LocalInterface
    {
        return $this->local;
    }

    public function getNumeroLocal(): ?int
    {
        return $this->numeroLocal;
    }

    public function setLocal(?LocalInterface $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function setNumeroLocal(?int $numeroLocal): static
    {
        $this->numeroLocal = $numeroLocal;

        return $this;
    }

    public function getDataAgendamento(): ?DateTimeInterface
    {
        return $this->dataAgendamento;
    }

    public function setDataAgendamento(?DateTimeInterface $dataAgendamento): static
    {
        $this->dataAgendamento = $dataAgendamento;

        return $this;
    }

    public function getDataChegada(): ?DateTimeInterface
    {
        return $this->dataChegada;
    }

    public function setDataChegada(?DateTimeInterface $dataChegada): static
    {
        $this->dataChegada = $dataChegada;

        return $this;
    }

    public function getDataChamada(): ?DateTimeInterface
    {
        return $this->dataChamada;
    }

    public function setDataChamada(?DateTimeInterface $dataChamada): static
    {
        $this->dataChamada = $dataChamada;

        return $this;
    }

    public function getDataInicio(): ?DateTimeInterface
    {
        return $this->dataInicio;
    }

    public function setDataInicio(?DateTimeInterface $dataInicio): static
    {
        $this->dataInicio = $dataInicio;

        return $this;
    }

    public function getDataFim(): ?DateTimeInterface
    {
        return $this->dataFim;
    }

    public function setDataFim(?DateTimeInterface $dataFim): static
    {
        $this->dataFim = $dataFim;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getResolucao(): ?string
    {
        return $this->resolucao;
    }

    public function setResolucao(?string $resolucao): static
    {
        $this->resolucao = $resolucao;

        return $this;
    }

    public function setCliente(?ClienteInterface $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function setTempoEspera(DateInterval $tempoEspera): static
    {
        $this->tempoEspera = $this->dateIntervalToSeconds($tempoEspera);

        return $this;
    }

    /**
     * Retorna o tempo de espera do cliente até ser atendido.
     * A diferença entre a data de chegada até a data de chamada (ou atual).
     */
    public function getTempoEspera(): DateInterval
    {
        if ($this->tempoEspera) {
            return $this->secondsToDateInterval($this->tempoEspera);
        }

        $now = new DateTimeImmutable();
        $interval = $now->diff($this->getDataChegada());

        return $interval;
    }

    public function setTempoPermanencia(DateInterval $tempoPermanencia): static
    {
        $this->tempoPermanencia = $this->dateIntervalToSeconds($tempoPermanencia);

        return $this;
    }

    /**
     * Retorna o tempo de permanência do cliente na unidade.
     * A diferença entre a data de chegada até a data de fim de atendimento.
     */
    public function getTempoPermanencia(): DateInterval
    {
        if ($this->tempoPermanencia) {
            return $this->secondsToDateInterval($this->tempoPermanencia);
        }

        $interval = new DateInterval('P0M');
        if ($this->getDataFim()) {
            $interval = $this->getDataFim()->diff($this->getDataChegada());
        }

        return $interval;
    }

    public function setTempoAtendimento(?DateInterval $tempoAtendimento): static
    {
        $this->tempoAtendimento = $this->dateIntervalToSeconds($tempoAtendimento);

        return $this;
    }

    /**
     * Retorna o tempo total do atendimento.
     * A diferença entre a data de início e fim do atendimento.
     */
    public function getTempoAtendimento(): DateInterval
    {
        if ($this->tempoAtendimento) {
            return $this->secondsToDateInterval($this->tempoAtendimento);
        }

        $interval = new DateInterval('P0M');
        if ($this->getDataFim()) {
            $interval = $this->getDataFim()->diff($this->getDataInicio());
        }

        return $interval;
    }

    public function setTempoDeslocamento(DateInterval $tempoDeslocamento): static
    {
        $this->tempoDeslocamento = $this->dateIntervalToSeconds($tempoDeslocamento);

        return $this;
    }

    /**
     * Retorna o tempo de deslocamento do cliente.
     * A diferença entre a data de chamada até a data de início.
     */
    public function getTempoDeslocamento(): DateInterval
    {
        if ($this->tempoDeslocamento) {
            return $this->secondsToDateInterval($this->tempoDeslocamento);
        }

        $interval = new DateInterval('P0M');
        if ($this->getDataChamada()) {
            $interval = $this->getDataInicio()->diff($this->getDataChamada());
        }

        return $interval;
    }

    public function getCliente(): ?ClienteInterface
    {
        return $this->cliente;
    }

    public function getSenha(): SenhaInterface
    {
        return $this->senha;
    }

    public function getPrioridade(): ?PrioridadeInterface
    {
        return $this->prioridade;
    }

    public function setPrioridade(?PrioridadeInterface $prioridade): static
    {
        $this->prioridade = $prioridade;

        return $this;
    }

    public function getObservacao(): ?string
    {
        return $this->observacao;
    }

    public function setObservacao(?string $observacao): static
    {
        $this->observacao = $observacao;

        return $this;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'senha' => $this->getSenha(),
            'servico' => [
                'id' => $this->getServico()->getId(),
                'nome' => $this->getServico()->getNome(),
            ],
            'observacao' => $this->getObservacao(),
            'dataChegada' => $this->getDataChegada()->format('Y-m-d\TH:i:s'),
            'dataChamada' => $this->getDataChamada()?->format('Y-m-d\TH:i:s'),
            'dataInicio' => $this->getDataInicio()?->format('Y-m-d\TH:i:s'),
            'dataFim' => $this->getDataFim()?->format('Y-m-d\TH:i:s'),
            'dataAgendamento' => $this->getDataAgendamento()?->format('Y-m-d\TH:i:s'),
            'tempoEspera' => $this->getTempoEspera()->format('%H:%I:%S'),
            'prioridade' => $this->getPrioridade(),
            'status' => $this->getStatus(),
            'resolucao' => $this->getResolucao(),
            'cliente' => $this->getCliente(),
            'triagem' => $this->getUsuarioTriagem()?->getLogin(),
            'usuario' => $this->getUsuario()?->getLogin(),
        ];
    }

    private function dateIntervalToSeconds(DateInterval $d): int
    {
        return $d->s + ($d->i * 60) + ($d->h * 3600) + ($d->d * 86400) + ($d->m * 2592000);
    }

    private function secondsToDateInterval(int $s): DateInterval
    {
        $dt1 = new DateTimeImmutable("@0");
        $dt2 = new DateTimeImmutable("@{$s}");

        return $dt1->diff($dt2);
    }

    public function __toString()
    {
        return (string) $this->getSenha();
    }
}
