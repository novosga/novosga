<?php

namespace AppBundle\Entity;

/**
 * AbstractAtendimentoMeta
 * Atendimento metadata.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AbstractAtendimentoMeta extends Metadata
{
    abstract public function getAtendimento();

    abstract public function setAtendimento(AbstractAtendimento $atendimento);

    public function getEntity()
    {
        return $this->getAtendimento();
    }

    public function setEntity($entity)
    {
        $this->setAtendimento($entity);
    }
}
