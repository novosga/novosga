<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass="Novosga\Repository\LotacaoRepository")
 * @ ORM\Table(name="usu_grup_cargo")
 */
class Lotacao extends Model implements \JsonSerializable
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Usuario", inversedBy="lotacoes")
     * @ ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)
     */
    protected $usuario;

    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Grupo")
     * @ ORM\JoinColumn(name="grupo_id", referencedColumnName="id", nullable=false)
     */
    protected $grupo;

    /**
     * @ ORM\ManyToOne(targetEntity="Cargo")
     * @ ORM\JoinColumn(name="cargo_id", referencedColumnName="id", nullable=false)
     */
    protected $cargo;

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
