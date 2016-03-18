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
    private $codigo;

    /**
     * @var string
     */
    private $nome;

    /**
     * @var bool
     */
    private $status;

    /**
     * @var Grupo
     */
    private $grupo;

    /**
     * @var string
     */
    private $mensagemImpressao;

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

    public function getMensagemImpressao()
    {
        return $this->mensagemImpressao;
    }

    public function setMensagemImpressao($mensagemImpressao)
    {
        $this->mensagemImpressao = $mensagemImpressao;
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
            'status'            => $this->getStatus() == true,
            'mensagemImpressao' => $this->getMensagemImpressao(),
        ];
    }
}
