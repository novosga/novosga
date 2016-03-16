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
    private $unidade;

    /**
     * @var Servico
     */
    private $servico;

    /**
     * @var int
     */
    private $numero;


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
     * Get the value of Atual
     *
     * @return int
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set the value of Atual
     *
     * @param int atual
     *
     * @return self
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'numero' => $this->getNumero(),
            'servico' => $this->getServico()
        ];
    }

}
