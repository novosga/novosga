<?php

namespace Novosga\Entity;

/**
 * Definição de onde o usuário está lotado
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Lotacao extends Model implements \JsonSerializable
{
    /**
     * @var Usuario
     */
    private $usuario;

    /**
     * @var Grupo
     */
    private $grupo;

    /**
     * @var Cargo
     */
    private $cargo;

    public function __construct()
    {
    }

    /**
     * Modifica usuario.
     *
     * @param $usuario
     *
     * @return none
     */
    public function setUsuario(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Retorna objeto usuario.
     *
     * @return Usuario $usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Modifica grupo.
     *
     * @param $grupo
     *
     * @return none
     */
    public function setGrupo(Grupo $grupo)
    {
        $this->grupo = $grupo;
    }

    /**
     * Retorna objeto Grupo.
     *
     * @return Grupo $grupo
     */
    public function getGrupo()
    {
        return $this->grupo;
    }

    /**
     * Modifica cargo.
     *
     * @param $cargo
     *
     * @return none
     */
    public function setCargo(Cargo $cargo)
    {
        $this->cargo = $cargo;
    }

    /**
     * Retorna objeto Cargo.
     *
     * @return Cargo $cargo
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    public function jsonSerialize()
    {
        return [
            'cargo'   => $this->getCargo(),
            'grupo'   => $this->getGrupo(),
            'usuario' => $this->getUsuario(),
        ];
    }
}
