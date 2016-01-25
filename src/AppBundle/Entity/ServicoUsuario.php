<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Servico Usuario
 * Configuração do serviço que o usuário atende
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicoUsuario extends Model
{
    // (bug ao tentar mapear ServicoUnidade: composite primary key as part of the primary key of another entity)

    /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var Unidade
     */
    protected $unidade;

    /**
     * @var Usuario
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
