<?php

namespace Novosga\Model;

/**
 * @Entity
 * @Table(name="servicos")
 */
class Servico extends SequencialModel
{
    /**
     * @Column(type="string", name="nome", length=50, nullable=false)
     */
    protected $nome;

    /**
     * @Column(type="string", name="descricao", length=100, nullable=false)
     */
    protected $descricao;

    /**
     * @Column(type="smallint", name="status", nullable=false)
     */
    protected $status;

    /**
     * @Column(type="smallint", name="peso", nullable=false)
     */
    protected $peso;

    /**
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="macro_id", referencedColumnName="id")
     */
    protected $mestre;

    /**
     * @OneToMany(targetEntity="Servico", mappedBy="mestre")
     */
    protected $subServicos;

    /**
     * @OneToMany(targetEntity="ServicoUnidade", mappedBy="servico")
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
        return array(
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'peso' => $this->getPeso(),
            'status' => $this->getStatus(),
            'macro' => $this->getMestre(),
        );
    }
}
