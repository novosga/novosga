<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity(repositoryClass="Novosga\Repository\PermissaoRepository")
 * @ ORM\Table(name="cargos_mod_perm")
 */
class Permissao extends Model implements \JsonSerializable
{
    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Modulo")
     * @ ORM\JoinColumn(name="modulo_id", referencedColumnName="id")
     */
    protected $modulo;

    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Cargo", inversedBy="permissoes")
     * @ ORM\JoinColumn(name="cargo_id", referencedColumnName="id")
     */
    protected $cargo;

    /**
     * @ ORM\Column(type="integer", name="permissao", nullable=false)
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
