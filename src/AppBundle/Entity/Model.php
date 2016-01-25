<?php

namespace AppBundle\Entity;

/**
 * Modelo abstrato.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class Model
{
    /**
     * @return string
     */
    public function toString()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    public function __tostring()
    {
        return $this->toString();
    }
}
