<?php
namespace novosga\model;

/**
 * 
 * @Entity
 * @Table(name="painel_senha")
 * 
 * @author rogeriolino
 */ 
 class PainelSenha extends SequencialModel {
 	
    /**
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;
    /**
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="unidade_id", referencedColumnName="id")
     */
    protected $unidade;
    
    /** @Column(type="integer", name="num_senha", nullable=false) */
    protected $numeroSenha;
    
    /** @Column(type="string", name="sig_senha", length=1, nullable=false) */
    protected $siglaSenha;
    
    /** @Column(type="string", name="msg_senha", length=20, nullable=false) */
    protected $mensagem;
    
    /** @Column(type="string", name="local", length=15, nullable=false) */
    protected $local;
    
    /** @Column(type="integer", name="num_local", nullable=false) */
    protected $numeroLocal;
    
    /** @Column(type="integer", name="peso", nullable=false) */
    protected $peso;
    
    
    public function getServico() {
        return $this->servico;
    }

    public function setServico($servico) {
        $this->servico = $servico;
    }

    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade($unidade) {
        $this->unidade = $unidade;
    }

    public function getNumeroSenha() {
        return $this->numeroSenha;
    }

    public function setNumeroSenha($numeroSenha) {
        $this->numeroSenha = $numeroSenha;
    }

    public function getSiglaSenha() {
        return $this->siglaSenha;
    }

    public function setSiglaSenha($siglaSenha) {
        $this->siglaSenha = $siglaSenha;
    }

    public function getMensagem() {
        return $this->mensagem;
    }

    public function setMensagem($mensagem) {
        $this->mensagem = $mensagem;
    }

    public function getLocal() {
        return $this->local;
    }

    public function setLocal($local) {
        $this->local = $local;
    }

    public function getNumeroLocal() {
        return $this->numeroLocal;
    }

    public function setNumeroLocal($numeroLocal) {
        $this->numeroLocal = $numeroLocal;
    }
    
    public function getPeso() {
        return $this->peso;
    }

    public function setPeso($peso) {
        $this->peso = $peso;
    }

    public function toArray() {
        return array(
            'id' => $this->getId(),
            'senha' => $this->getSiglaSenha() . str_pad($this->getNumeroSenha(), 3, '0', STR_PAD_LEFT),
            'local' => $this->getLocal(),
            'numeroLocal' => $this->getNumeroLocal(),
            'peso' => $this->getPeso()
        );
    }

}
