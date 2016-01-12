<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractAtendimentoCodificado
 * atendimento codificado (servico realizado).
 *
 * @ ORM\MappedSuperclass
 */
abstract class AbstractAtendimentoCodificado extends Model
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Servico")
     * @ ORM\JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;

    /**
     * @ ORM\Column(type="smallint", name="valor_peso", nullable=false)
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
