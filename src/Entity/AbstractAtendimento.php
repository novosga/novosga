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

use DateTime;
use DateInterval;
use App\Entity\Cliente;
use App\Entity\Senha;
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
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Unidade $unidade = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Servico $servico = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prioridade $prioridade = null;

    #[ORM\ManyToOne]
    protected ?Usuario $usuario = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'usuario_tri_id', nullable: false)]
    protected ?Usuario $usuarioTriagem = null;

    #[ORM\ManyToOne]
    protected ?Local $local = null;

    #[ORM\Column(name: 'num_local', nullable: true)]
    protected ?int $numeroLocal = null;

    #[ORM\Column(name: 'dt_age', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $dataAgendamento = null;

    #[ORM\Column(name: 'dt_cheg', type: Types::DATETIME_MUTABLE)]
    protected ?DateTime $dataChegada = null;

    #[ORM\Column(name: 'dt_cha', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $dataChamada = null;

    #[ORM\Column(name: 'dt_ini', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $dataInicio = null;

    #[ORM\Column(name: 'dt_fim', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $dataFim = null;

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
    
    #[ORM\ManyToOne(cascade: ['persist'])]
    protected ?Cliente $cliente = null;
    
    #[ORM\Embedded(columnPrefix: 'senha_')]
    protected Senha $senha;
    
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
    
    public function getDataAgendamento(): ?DateTime
    {
        return $this->dataAgendamento;
    }

    public function setDataAgendamento(?DateTime $dataAgendamento): static
    {
        $this->dataAgendamento = $dataAgendamento;
        
        return $this;
    }

    public function getDataChegada(): ?DateTime
    {
        return $this->dataChegada;
    }

    public function setDataChegada(?DateTime $dataChegada): static
    {
        $this->dataChegada = $dataChegada;

        return $this;
    }

    public function getDataChamada(): ?DateTime
    {
        return $this->dataChamada;
    }

    public function setDataChamada(?DateTime $dataChamada): static
    {
        $this->dataChamada = $dataChamada;

        return $this;
    }

    public function getDataInicio(): ?DateTime
    {
        return $this->dataInicio;
    }

    public function setDataInicio(?DateTime $dataInicio): static
    {
        $this->dataInicio = $dataInicio;

        return $this;
    }

    public function getDataFim(): ?DateTime
    {
        return $this->dataFim;
    }

    public function setDataFim(?DateTime $dataFim): static
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
        
    public function setCliente(ClienteInterface $cliente): static
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
        
        $now = new DateTime();
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

    public function setTempoAtendimento(DateInterval $tempoAtendimento): static
    {
        if ($tempoAtendimento) {
            $this->tempoAtendimento = $this->dateIntervalToSeconds($tempoAtendimento);
        } else {
            $this->tempoAtendimento = 0;
        }
        
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
     *
     * @return \DateInterval
     */
    public function getTempoDeslocamento()
    {
        if ($this->tempoDeslocamento) {
            return $this->secondsToDateInterval($this->tempoDeslocamento);
        }
        
        $interval = new \DateInterval('P0M');
        if ($this->getDataChamada()) {
            $interval = $this->getDataInicio()->diff($this->getDataChamada());
        }

        return $interval;
    }

    public function getCliente(): ?Cliente
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
    
    public function jsonSerialize()
    {
        return [
            'id'       => $this->getId(),
            'senha'    => $this->getSenha(),
            'servico'  => [
                'id'   => $this->getServico()->getId(),
                'nome' => $this->getServico()->getNome(),
            ],
            'observacao'      => $this->getObservacao(),
            'dataChegada'     => $this->getDataChegada()->format('Y-m-d\TH:i:s'),
            'dataChamada'     => $this->getDataChamada() ? $this->getDataChamada()->format('Y-m-d\TH:i:s') : null,
            'dataInicio'      => $this->getDataInicio() ? $this->getDataInicio()->format('Y-m-d\TH:i:s') : null,
            'dataFim'         => $this->getDataFim() ? $this->getDataFim()->format('Y-m-d\TH:i:s') : null,
            'dataAgendamento' => $this->getDataAgendamento() ? $this->getDataAgendamento()->format('Y-m-d\TH:i:s') : null,
            'tempoEspera'     => $this->getTempoEspera()->format('%H:%I:%S'),
            'prioridade'      => $this->getPrioridade(),
            'status'          => $this->getStatus(),
            'resolucao'       => $this->getResolucao(),
            'cliente'         => $this->getCliente(),
            'triagem'         => $this->getUsuarioTriagem() ? $this->getUsuarioTriagem()->getLogin() : null,
            'usuario'         => $this->getUsuario() ? $this->getUsuario()->getLogin() : null,
        ];
    }

    public function __toString()
    {
        return (string) $this->getSenha();
    }

    private function dateIntervalToSeconds(DateInterval $d): int
    {
        return $d->s + ($d->i * 60) + ($d->h * 3600) + ($d->d * 86400) + ($d->m * 2592000);
    }
    
    private function secondsToDateInterval(int $s)
    {
        $dt1 = new \DateTime("@0");
        $dt2 = new \DateTime("@{$s}");

        return $dt1->diff($dt2);
    }
}
