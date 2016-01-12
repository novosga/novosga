<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ ORM\Entity
 * @ ORM\Table(name="usu_serv")
 */
class ServicoUsuario extends Model
{
    // (bug ao tentar mapear ServicoUnidade: composite primary key as part of the primary key of another entity)

    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Servico")
     * @ ORM\JoinColumn(name="servico_id", referencedColumnName="id", nullable=false)
     */
    protected $servico;

    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Unidade")
     * @ ORM\JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     */
    protected $unidade;

    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Usuario")
     * @ ORM\JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)
     */
    protected $usuario;

    public function __construct()
    {
    }

    public function getServico()
    {
        return $this->servico;
    }

    public function setServico($servico)
    {
        $this->servico = $servico;
    }

    public function getUnidade()
    {
        return $this->unidade;
    }

    public function setUnidade($unidade)
    {
        $this->unidade = $unidade;
    }

    /**
     * @return Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }
}
