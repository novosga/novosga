<?php
namespace novosga\model;

/**
 * @Entity
 * @Table(name="servicos")
 */
class Servico extends SequencialModel {
    
    /** @Column(type="string", name="nome", length=50, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="descricao", length=100, nullable=false) */
    protected $descricao;
    /** @Column(type="integer", name="status", nullable=false) */
    protected $status;
    /** 
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="id_macro", referencedColumnName="id")
     */
    protected $mestre;
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
