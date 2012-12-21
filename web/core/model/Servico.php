<?php
namespace core\model;

use \core\model\SequencialModel;
use \core\model\ServicoUnidade;

/**
 * @Entity
 * @Table(name="servicos")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_serv",type="integer")
 *      )
 * })
 */
class Servico extends SequencialModel {
    
    /** @Column(type="string", name="nm_serv", length=50, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="desc_serv", length=100, nullable=false) */
    protected $descricao;
    /** @Column(type="integer", name="stat_serv", nullable=false) */
    protected $status;
    /** 
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="id_macro", referencedColumnName="id_serv")
     */
    private $mestre;
    /** 
     * @OneToMany(targetEntity="Servico", mappedBy="mestre")
     */
    protected $subServicos;
    /** 
     * @OneToMany(targetEntity="ServicoUnidade", mappedBy="servico")
     */
    protected $servicosUnidade;
	
    public function __construct() {
    }
    
    public function setNome($nome) {
        $this->nome = $nome;
    }
    
    public function getNome() {
        return $this->nome;
    }
    
    public function setDescricao($desc) {
        $this->descricao = $desc;
    }
    
    public function getDescricao() {
        return $this->descricao;
    }
    
    public function setMestre(Servico $servico = null) {
        $this->mestre = $servico;
    }
    
    public function getMestre() {
        return $this->mestre;
    }
    
    public function isMestre() {
        return ($this->mestre == 0) ? true : false;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }
    
    public function getSubServicos() {
        return $this->subServicos;
    }

    public function setSubServicos($subServicos) {
        $this->subServicos = $subServicos;
    }
        
    public function getServicosUnidade() {
        return $this->servicosUnidade;
    }

    public function setServicosUnidade(array $servicosUnidade) {
        $this->servicosUnidade = $servicosUnidade;
    }

    public function toString() {
        return $this->nome;
    }
	
}
