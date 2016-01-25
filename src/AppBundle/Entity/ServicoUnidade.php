<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Servico Unidade
 * Configuração do serviço na unidade
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ServicoUnidade extends Model implements \JsonSerializable
{
    /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var Unidade
     */
    protected $unidade;

    /**
     * @var Local
     */
    protected $local;

    /**
     * @var string
     */
    protected $sigla;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var int
     */
    protected $peso;

    public function __construct()
    {
    }

    /**
     * @return Servico
     */
    public function getServico()
    {
        return $this->servico;
    }

    public function setServico(Servico $servico)
    {
        $this->servico = $servico;
    }

    /**
     * @return Unidade
     */
    public function getUnidade()
    {
        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade)
    {
        $this->unidade = $unidade;
    }

    /**
     * @return Local
     */
    public function getLocal()
    {
        return $this->local;
    }

    public function setLocal(Local $local)
    {
        $this->local = $local;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function setPeso($peso)
    {
        $this->peso = $peso;
    }

    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
    }

    public function getSigla()
    {
        return $this->sigla;
    }

    public function toString()
    {
        return $this->sigla.' - '.$this->getServico()->getNome();
    }

    public function jsonSerialize()
    {
        return [
            'sigla'   => $this->getSigla(),
            'peso'    => $this->getPeso(),
            'local'   => $this->getLocal(),
            'servico' => $this->getServico(),
        ];
    }
}
