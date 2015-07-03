<?php

namespace Novosga\Model;

/**
 * Model Sequencial (id numérico).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 *
 * @MappedSuperClass
 */
abstract class SequencialModel extends Model  implements \JsonSerializable
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer", name="id", nullable=false)
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
        return array(
            'id' => $this->getId(),
        );
    }
}
