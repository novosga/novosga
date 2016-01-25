<?php

namespace AppBundle\Entity;

/**
 * Permissao
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Permissao extends Model implements \JsonSerializable
{
    /**
     * @var Modulo
     */
    protected $modulo;

    /**
     * @var Cargo
     */
    protected $cargo;

    /**
     * @var int
     */
    protected $permissao;

    public function __construct()
    {
    }

    /**
     * Define o modulo ao qual a permissão se refere.
     *
     * @param Modulo $modulo
     */
    public function setModulo(Modulo $modulo)
    {
        $this->modulo = $modulo;
    }

    /**
     * Retorna o modulo ao qual esta permissão se refere.
     *
     * @return Modulo
     */
    public function getModulo()
    {
        return $this->modulo;
    }

    public function getCargo()
    {
        return $this->cargo;
    }

    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    }

    public function getPermissao()
    {
        return $this->permissao;
    }

    public function setPermissao($permissao)
    {
        $this->permissao = $permissao;
    }

    public function jsonSerialize()
    {
        return [
            'cargo'     => $this->getCargo(),
            'modulo'    => $this->getModulo(),
            'permissao' => $this->getPermissao(),
        ];
    }
}
