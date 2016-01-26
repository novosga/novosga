<?php

namespace Novosga\Entity;

/**
 * Ticket counter.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Contador extends Model implements \JsonSerializable
{
    /**
     * @var Unidade
     */
    protected $unidade;

    /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var int
     */
    private $incremento;

    /**
     * @var int
     */
    private $minimo;

    /**
     * @var int
     */
    private $maximo;

    /**
     * @var int
     */
    private $atual;

    public function __construct()
    {
        $this->minimo = 1;
        $this->incremento = 1;
        $this->atual = $this->minimo;
    }

    /**
     * Get the value of Unidade
     *
     * @return Unidade
     */
    public function getUnidade()
    {
        return $this->unidade;
    }

    /**
     * Set the value of Unidade
     *
     * @param Unidade unidade
     *
     * @return self
     */
    public function setUnidade(Unidade $unidade)
    {
        $this->unidade = $unidade;

        return $this;
    }

    /**
     * Get the value of Servico
     *
     * @return Servico
     */
    public function getServico()
    {
        return $this->servico;
    }

    /**
     * Set the value of Servico
     *
     * @param Servico servico
     *
     * @return self
     */
    public function setServico(Servico $servico)
    {
        $this->servico = $servico;

        return $this;
    }

    /**
     * Get the value of Incremento
     *
     * @return int
     */
    public function getIncremento()
    {
        return $this->incremento;
    }

    /**
     * Set the value of Incremento
     *
     * @param int incremento
     *
     * @return self
     */
    public function setIncremento($incremento)
    {
        $this->incremento = $incremento;

        return $this;
    }

    /**
     * Get the value of Minimo
     *
     * @return int
     */
    public function getMinimo()
    {
        return $this->minimo;
    }

    /**
     * Set the value of Minimo
     *
     * @param int minimo
     *
     * @return self
     */
    public function setMinimo($minimo)
    {
        $this->minimo = $minimo;

        return $this;
    }

    /**
     * Get the value of Maximo
     *
     * @return int
     */
    public function getMaximo()
    {
        return $this->maximo;
    }

    /**
     * Set the value of Maximo
     *
     * @param int maximo
     *
     * @return self
     */
    public function setMaximo($maximo)
    {
        $this->maximo = $maximo;

        return $this;
    }

    /**
     * Get the value of Atual
     *
     * @return int
     */
    public function getAtual()
    {
        return $this->atual;
    }

    /**
     * Set the value of Atual
     *
     * @param int atual
     *
     * @return self
     */
    public function setAtual($atual)
    {
        $this->atual = $atual;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'minimo' => $this->getMinimo(),
            'maximo' => $this->getMaximo(),
            'incremento' => $this->getIncremento(),
            'atual' => $this->getAtual(),
        ];
    }

}
