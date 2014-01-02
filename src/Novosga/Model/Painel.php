<?php
namespace Novosga\Model;

/**
 * 
 * @Entity
 * @Table(name="paineis")
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */ 
 class Painel extends Model {
 	
    /**
     * @Id  
     * @Column(type="integer", name="host", nullable=false) 
     */
    protected $host;
    /**
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id", nullable=false)
     */
    protected $unidade;
    /** 
     * @OneToMany(targetEntity="PainelServico", mappedBy="painel")
     */
    protected $servicos;

    
    public function getHost() {
        return $this->host;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade($unidade) {
        $this->unidade = $unidade;
    }
    
    public function getServicos() {
        return $this->servicos;
    }

    public function setServicos($servicos) {
        $this->servicos = $servicos;
    }
    
    public function getIp() {
        return long2ip($this->getHost());
    }
    
    public function toString() {
        return $this->getIp();
    }

}
