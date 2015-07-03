<?php

namespace Novosga\Model;

/**
 * AbstractAtendimentoCodificado
 * atendimento codificado (servico realizado).
 *
 * @MappedSuperClass
 */
abstract class AbstractAtendimentoCodificado extends Model
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;

    /**
     * @Column(type="smallint", name="valor_peso", nullable=false)
     */
    protected $peso;

    abstract public function getAtendimento();
    abstract public function setAtendimento(AbstractAtendimento $atendimento);

    public function getServico()
    {
        return $this->servico;
    }

    public function setServico($servico)
    {
        $this->servico = $servico;
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function setPeso($peso)
    {
        $this->peso = $peso;
    }
}
