<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Unidade metadata.
 *
 * @ ORM\MappedSuperclass
 */
abstract class Metadata extends Model implements \JsonSerializable
{
    /**
     * @ ORM\Id
     * @ ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    protected $name;

    /**
     * @ ORM\Column(name="value", type="string", columnDefinition="text")
     */
    protected $value;

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
