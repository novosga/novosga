<?php

namespace Novosga\Entity;

/**
 * Usuario metadata.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UsuarioMeta extends Metadata
{
    /**
     * @var Usuario
     */
    protected $usuario;

    public function getEntity()
    {
        return $this->getUsuario();
    }

    public function setEntity($entity)
    {
        $this->setUsuario($entity);
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }
}
