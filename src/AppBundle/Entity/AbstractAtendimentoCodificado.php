<?php

namespace AppBundle\Entity;

/**
 * AbstractAtendimentoCodificado
 * atendimento codificado (servico realizado).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AbstractAtendimentoCodificado extends Model
{
    /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var int
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
