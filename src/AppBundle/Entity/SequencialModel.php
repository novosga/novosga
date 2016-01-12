<?php

namespace AppBundle\Entity;

/**
 * Model Sequencial (id numérico).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class SequencialModel extends Model  implements \JsonSerializable
{
    /**
     */
    protected $id = 0;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return int
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
