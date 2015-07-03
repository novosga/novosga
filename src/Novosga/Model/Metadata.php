<?php

namespace Novosga\Model;

/**
 * Unidade metadata.
 *
 * @MappedSuperclass
 */
abstract class Metadata extends Model implements \JsonSerializable
{
    /**
     * @Id
     * @Column(name="name", type="string", length=50, nullable=false)
     */
    protected $name;

    /**
     * @Column(name="value", type="string", columnDefinition="text")
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
        return array(
            'name' => $this->getName(),
            'value' => $this->getValue(),
        );
    }
}
