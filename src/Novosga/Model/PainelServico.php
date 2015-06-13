<?php

namespace Novosga\Model;

/**
  * @Entity
  * @Table(name="paineis_servicos")
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
 class PainelServico extends Model implements \JsonSerializable
 {
     /**
     * @Id
     * @ManyToOne(targetEntity="Painel")
     * @JoinColumn(name="host", referencedColumnName="host")
     */
    protected $painel;

    /**
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;

    /**
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id")
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
         return array(
            'painel' => $this->getPainel(),
            'servico' => $this->getServico(),
            'unidade' => $this->getUnidade(),
        );
     }
 }
