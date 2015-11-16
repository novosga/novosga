<?php

namespace Novosga\Model;

/**
 * Usuario metadata.
 *
 * @Entity
 * @Table(name="usu_meta")
 */
class UsuarioMeta extends Metadata
{
    /**
     * @Id
     * @ManyToOne(targetEntity="Usuario")
     * @JoinColumn(name="usuario_id", referencedColumnName="id")
     *
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
