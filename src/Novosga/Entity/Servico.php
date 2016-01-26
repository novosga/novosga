<?php

namespace Novosga\Entity;

/**
 * Servico
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Servico extends SequencialModel
{
    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string
     */
    protected $descricao;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $peso;

    /**
     * @var Servico
     */
    protected $mestre;

    /**
     * @var Servico
     */
    protected $subServicos;

    /**
     * @var ServicoUnidade
     */
    protected $servicosUnidade;

    public function __construct()
    {
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setDescricao($desc)
    {
        $this->descricao = $desc;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setMestre(Servico $servico = null)
    {
        $this->mestre = $servico;
    }

    public function getMestre()
    {
        return $this->mestre;
    }

    public function isMestre()
    {
        return ($this->mestre == 0) ? true : false;
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

    public function getSubServicos()
    {
        return $this->subServicos;
    }

    public function setSubServicos($subServicos)
    {
        $this->subServicos = $subServicos;
    }

    public function getServicosUnidade()
    {
        return $this->servicosUnidade;
    }

    public function setServicosUnidade(array $servicosUnidade)
    {
        $this->servicosUnidade = $servicosUnidade;
    }

    public function toString()
    {
        return $this->nome;
    }

    public function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'peso'      => $this->getPeso(),
            'status'    => $this->getStatus(),
            'macro'     => $this->getMestre(),
        ];
    }
}
