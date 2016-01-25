<?php

namespace AppBundle\Entity;

/**
 * Unidade metadata.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadeMeta extends Metadata
{
    /**
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
