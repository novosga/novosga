<?php

namespace Novosga\Entity;

/**
  * PainelServico
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
class PainelServico extends Model implements \JsonSerializable
{
    /**
     * @var Painel
     */
    protected $painel;

    /**
     * @var Servico
     */
    protected $servico;

    /**
     * @var Unidade
     */
    protected $unidade;

    public function getPainel()
    {
        return $this->painel;
    }

    public function setPainel($painel)
    {
        $this->painel = $painel;
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

    public function jsonSerialize()
    {
        return [
           'painel'  => $this->getPainel(),
           'servico' => $this->getServico(),
           'unidade' => $this->getUnidade(),
       ];
    }
 }
