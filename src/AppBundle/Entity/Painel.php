<?php

namespace AppBundle\Entity;

/**
  * Painel
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
 class Painel extends Model implements \JsonSerializable
 {
     /**
     * @var int
     */
    protected $host;

     /**
     * @var string
     */
    protected $senha;

    /**
     * @var Unidade
     */
    protected $unidade;

    /**
     * @var PainelServico[]
     */
    protected $servicos;

     public function getHost()
     {
         return $this->host;
     }

     public function setHost($host)
     {
         $this->host = $host;
     }

     public function getUnidade()
     {
         return $this->unidade;
     }

     public function setUnidade($unidade)
     {
         $this->unidade = $unidade;
     }

     public function getServicos()
     {
         return $this->servicos;
     }

     public function setServicos($servicos)
     {
         $this->servicos = $servicos;
     }

     public function getIp()
     {
         return long2ip($this->getHost());
     }

     public function toString()
     {
         return $this->getIp();
     }

     public function jsonSerialize()
     {
         return [
            'host'     => $this->getHost(),
            'ip'       => $this->getIp(),
            'servicos' => $this->getServicos(),
        ];
     }
 }
