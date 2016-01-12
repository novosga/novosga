<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
  * @ ORM\Entity
  * @ ORM\Table(name="paineis")
  *
  * @author Rogerio Lino <rogeriolino@gmail.com>
  */
 class Painel extends Model implements \JsonSerializable
 {
     /**
     * @ ORM\Id
     * @ ORM\Column(type="integer", name="host", nullable=false)
     */
    protected $host;
    
     /**
     * @ ORM\Column(type="string", name="senha", length=128, nullable=true)
     */
    protected $senha;

    /**
     * @ ORM\ManyToOne(targetEntity="Unidade")
     * @ ORM\JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     */
    protected $unidade;

    /**
     * @ ORM\OneToMany(targetEntity="PainelServico", mappedBy="painel")
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
