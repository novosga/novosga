<?php

namespace Novosga\Entity;

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
    private $unidade;

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
