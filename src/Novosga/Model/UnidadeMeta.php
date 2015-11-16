<?php

namespace Novosga\Model;

/**
 * Unidade metadata.
 *
 * @Entity
 * @Table(name="uni_meta")
 */
class UnidadeMeta extends Metadata
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id")
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
