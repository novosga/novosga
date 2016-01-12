<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Unidade metadata.
 *
 * @ ORM\Entity
 * @ ORM\Table(name="uni_meta")
 */
class UnidadeMeta extends Metadata
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Unidade")
     * @ ORM\JoinColumn(name="unidade_id", referencedColumnName="id")
     *
     * @var Unidade
     */
    protected $unidade;

    public function getEntity()
    {
        return $this->getUnidade();
    }

    public function setEntity($entity)
    {
        $this->setUnidade($entity);
    }

    public function getUnidade()
    {
        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade)
    {
        $this->unidade = $unidade;

        return $this;
    }
}
