<?php

namespace Novosga\Entity;

/**
 * Servico Unidade
 * Configuração do serviço na unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicoUnidade extends Model implements \JsonSerializable
{
    /**
     * @var Servico
     */
    private $servico;

    /**
     * @var Unidade
     */
    private $unidade;

    /**
     * @var Local
     */
    private $local;

    /**
     * @var string
     */
    private $sigla;

    /**
     * @var int
     */
    private $status;

    /**
     * @var int
     */
    private $peso;

    /**
     * @var int
     */
    private $tempoAtendimento;

    /**
     * @var int
     */
    private $horaInicio;

    /**
     * @var int
     */
    private $horaFim;

    /**
     * @var int
     */
    private $maximoAtendimentos;

    /**
     * @var bool
     */
    private $prioridade;
    
    /**
     * @var int
     */
    private $incremento;

    /**
     * @var int
     */
    private $numeroInicial;

    /**
     * @var int
     */
    private $numeroFinal;
    
    public function __construct()
    {
        $this->prioridade = true;
        $this->numeroInicial = 1;
        $this->incremento = 1;
    }

    /**
     * @return Servico
     */
    public function getServico()
    {
        return $this->servico;
    }

    public function setServico(Servico $servico)
    {
        $this->servico = $servico;
    }

    /**
     * @return Unidade
     */
    public function getUnidade()
    {
        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade)
    {
        $this->unidade = $unidade;
    }

    /**
     * @return Local
     */
    public function getLocal()
    {
        return $this->local;
    }

    public function setLocal(Local $local)
    {
        $this->local = $local;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function setPeso($peso)
    {
        $this->peso = $peso;
    }

    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
    }

    public function getSigla()
    {
        return $this->sigla;
    }
    
    public function getTempoAtendimento()
    {
        return $this->tempoAtendimento;
    }

    public function getHoraInicio()
    {
        return $this->horaInicio;
    }

    public function getHoraFim()
    {
        return $this->horaFim;
    }

    public function getMaximoAtendimentos()
    {
        return $this->maximoAtendimentos;
    }

    public function getPrioridade()
    {
        return $this->prioridade;
    }

    public function getIncremento()
    {
        return $this->incremento;
    }

    public function getNumeroInicial()
    {
        return $this->numeroInicial;
    }

    public function getNumeroFinal()
    {
        return $this->numeroFinal;
    }

    public function setTempoAtendimento($tempoAtendimento)
    {
        $this->tempoAtendimento = $tempoAtendimento;
        return $this;
    }

    public function setHoraInicio($horaInicio)
    {
        $this->horaInicio = $horaInicio;
        return $this;
    }

    public function setHoraFim($horaFim)
    {
        $this->horaFim = $horaFim;
        return $this;
    }

    public function setMaximoAtendimentos($maximoAtendimentos)
    {
        $this->maximoAtendimentos = $maximoAtendimentos;
        return $this;
    }

    public function setPrioridade($prioridade)
    {
        $this->prioridade = $prioridade;
        return $this;
    }

    public function setIncremento($incremento)
    {
        $this->incremento = $incremento;
        return $this;
    }

    public function setNumeroInicial($numeroInicial)
    {
        $this->numeroInicial = $numeroInicial;
        return $this;
    }

    public function setNumeroFinal($numeroFinal)
    {
        $this->numeroFinal = $numeroFinal;
        return $this;
    }

    public function toString()
    {
        return $this->sigla.' - '.$this->getServico()->getNome();
    }

    public function jsonSerialize()
    {
        return [
            'sigla'   => $this->getSigla(),
            'peso'    => $this->getPeso(),
            'local'   => $this->getLocal(),
            'servico' => $this->getServico(),
            'status'  => !!$this->getStatus(),
        ];
    }
}
