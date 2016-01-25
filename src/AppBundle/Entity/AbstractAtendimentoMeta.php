<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getEntity()
    {
        return $this->getAtendimento();
    }

    public function setEntity($entity)
    {
        $this->setAtendimento($entity);
    }
}
