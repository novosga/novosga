<?php

namespace Novosga\Entity;

/**
 * Abstract metadata.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class Metadata extends Model implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    public function __construct()
    {
    }

    abstract public function setEntity($entity);

    abstract public function getEntity();

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

    public function jsonSerialize()
    {
        return [
            'name'  => $this->getName(),
            'value' => $this->getValue(),
        ];
    }
}
