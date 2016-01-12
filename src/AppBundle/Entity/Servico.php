<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass="Novosga\Repository\ServicoRepository")
 * @ ORM\Table(name="servicos")
 */
class Servico extends SequencialModel
{
    /**
     * @ ORM\Column(type="string", name="nome", length=50, nullable=false)
     */
    protected $nome;

    /**
     * @ ORM\Column(type="string", name="descricao", length=100, nullable=false)
     */
    protected $descricao;

    /**
     * @ ORM\Column(type="smallint", name="status", nullable=false)
     */
    protected $status;

    /**
     * @ ORM\Column(type="smallint", name="peso", nullable=false)
     */
    protected $peso;

    /**
     * @ ORM\ManyToOne(targetEntity="Servico", inversedBy="subServicos")
     * @ ORM\JoinColumn(name="macro_id", referencedColumnName="id")
     */
    protected $mestre;

    /**
     * @ ORM\OneToMany(targetEntity="Servico", mappedBy="mestre")
     */
    protected $subServicos;

    /**
     * @ ORM\OneToMany(targetEntity="ServicoUnidade", mappedBy="servico")
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
