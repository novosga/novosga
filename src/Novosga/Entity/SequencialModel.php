<?php

namespace Novosga\Entity;

/**
 * Model Sequencial (id numérico).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class SequencialModel extends Model  implements \JsonSerializable
{
    /**
     * @var mixed
     */
    protected $id = 0;

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return get_class($this).'[id='.$this->id.']';
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
        ];
    }
}
