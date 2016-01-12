<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
  * @ ORM\Entity
  * @ ORM\Table(name="paineis_servicos")
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
 class PainelServico extends Model implements \JsonSerializable
 {
     /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Painel")
     * @ ORM\JoinColumn(name="host", referencedColumnName="host")
     */
    protected $painel;

    /**
     * @ ORM\Id
     * @ ORM\ManyToOne(targetEntity="Servico")
     * @ ORM\JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;

    /**
     * @ ORM\ManyToOne(targetEntity="Unidade")
     * @ ORM\JoinColumn(name="unidade_id", referencedColumnName="id")
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
