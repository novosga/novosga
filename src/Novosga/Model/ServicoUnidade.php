<?php

namespace Novosga\Model;

/**
 * Servico Unidade.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Entity
 * @Table(name="uni_serv")
 */
class ServicoUnidade extends Model implements \JsonSerializable
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id", nullable=false)
     */
    protected $servico;

    /**
     * @Id
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     */
    protected $unidade;

    /**
     * @ManyToOne(targetEntity="Local")
     * @JoinColumn(name="local_id", referencedColumnName="id", nullable=false)
     */
    protected $local;

    /**
     * @Column(type="string", name="sigla", length=1, nullable=false)
     */
    protected $sigla;

    /**
     * @Column(type="smallint", name="status", nullable=false)
     */
    protected $status;

    /**
     * @Column(type="smallint", name="peso", nullable=false)
     */
    protected $peso;

    public function __construct()
    {
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

    public function toString()
    {
        return $this->sigla.' - '.$this->getServico()->getNome();
    }

    public function jsonSerialize()
    {
        return array(
            'sigla' => $this->getSigla(),
            'peso' => $this->getPeso(),
            'local' => $this->getLocal(),
            'servico' => $this->getServico(),
        );
    }
}
