<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ticket counter.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @ ORM\Entity
 * @ ORM\Table(name="contador")
 */
class Contador extends Model implements \JsonSerializable
{
    /**
     * @ ORM\Id
     * @ ORM\OneToOne(targetEntity="Unidade", inversedBy="contador")
     * @ ORM\JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     *
     * @var Unidade
     */
    protected $unidade;

    /**
     * @ ORM\Column(type="integer", name="total", nullable=false)
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
        return [
            'total' => $this->getTotal(),
        ];
    }
}
