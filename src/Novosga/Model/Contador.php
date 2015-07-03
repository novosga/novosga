<?php

namespace Novosga\Model;

/**
 * Ticket counter.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @Entity
 * @Table(name="contador")
 */
class Contador extends Model implements \JsonSerializable
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     *
     * @var Unidade
     */
    protected $unidade;

    /**
     * @Column(type="integer", name="total", nullable=false)
     */
    private $total;

    public function __construct()
    {
    }

    public function getUnidade()
    {
        return $this->unidade;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setUnidade(Unidade $unidade)
    {
        $this->unidade = $unidade;

        return $this;
    }

    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    public function jsonSerialize()
    {
        return array(
            'total' => $this->getTotal(),
        );
    }
}
