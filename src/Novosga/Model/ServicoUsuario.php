<?php

namespace Novosga\Model;

/**
 * @Entity
 * @Table(name="usu_serv")
 */
class ServicoUsuario extends Model
{
    // (bug ao tentar mapear ServicoUnidade: composite primary key as part of the primary key of another entity)

    /**
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id", nullable=false)
     */
    protected $servico;

    /**
     * @Id
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     */
    protected $unidade;

    /**
     * @Id
     * @ManyToOne(targetEntity="Usuario")
     * @JoinColumn(name="usuario_id", referencedColumnName="id", nullable=false)
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
