<?php

namespace Novosga\Entity;

/**
 * Unidade de atendimento.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Unidade extends SequencialModel
{
    /**
     * @var string
     */
    protected $codigo;

    /**
     * @var string
     */
    protected $nome;

    /**
     * @var bool
     */
    protected $status;

    /**
     * @var Grupo
     */
    protected $grupo;

    /**
     * @var bool
     */
    protected $statusImpressao;

    /**
     * @var string
     */
    protected $mensagemImpressao;

    /**
     * @var Contador
     */
    protected $contador;

    public function __construct()
    {
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setGrupo(Grupo $grupo)
    {
        $this->grupo = $grupo;
    }

    /**
     * @return Grupo
     */
    public function getGrupo()
    {
        return $this->grupo;
    }

    public function getStatusImpressao()
    {
        return $this->statusImpressao;
    }

    public function setStatusImpressao($statusImpressao)
    {
        $this->statusImpressao = $statusImpressao;
    }

    public function getMensagemImpressao()
    {
        return $this->mensagemImpressao;
    }

    public function setMensagemImpressao($mensagemImpressao)
    {
        $this->mensagemImpressao = $mensagemImpressao;
    }

    public function getContador()
    {
        return $this->contador;
    }

    public function setContador(Contador $contador)
    {
        $this->contador = $contador;

        return $this;
    }

    public function toString()
    {
        return $this->getNome();
    }

    public function jsonSerialize()
    {
        return [
            'id'                => $this->getId(),
            'codigo'            => $this->getCodigo(),
            'nome'              => $this->getNome(),
            'grupo'             => $this->getGrupo(),
            'status'            => $this->getStatus(),
            'mensagemImpressao' => $this->getMensagemImpressao(),
            'statusImpressao'   => $this->getStatusImpressao(),
        ];
    }
}
