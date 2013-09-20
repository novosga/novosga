<?php
namespace novosga\model;

/**
 * 
 * @Entity
 * @Table(name="painel_senha")
 * 
 * @author rogeriolino
 */ 
 class PainelSenha extends Model {
 	
    /**
     * @Id
     * @Column(type="string", name="contador", nullable=false)
     */
    protected $id;
    /**
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="id_serv", referencedColumnName="id_serv")
     */
    protected $servico;
    /**
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="id_uni", referencedColumnName="id_uni")
     */
    protected $unidade;
    
    /** @Column(type="integer", name="num_senha", nullable=false) */
    protected $numeroSenha;
    
    /** @Column(type="string", name="sig_senha", length=1, nullable=false) */
    protected $siglaSenha;
    
    /** @Column(type="string", name="msg_senha", length=20, nullable=false) */
    protected $mensagem;
    
    /** @Column(type="string", name="nm_local", length=15, nullable=false) */
    protected $guiche;
    
    /** @Column(type="integer", name="num_guiche", nullable=false) */
    protected $numeroGuiche;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

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

    public function getGuiche() {
        return $this->guiche;
    }

    public function setGuiche($guiche) {
        $this->guiche = $guiche;
    }

    public function getNumeroGuiche() {
        return $this->numeroGuiche;
    }

    public function setNumeroGuiche($numeroGuiche) {
        $this->numeroGuiche = $numeroGuiche;
    }
    
    public function toArray() {
        return array(
            'id' => $this->getId(),
            'senha' => $this->getSiglaSenha() . str_pad($this->getNumeroSenha(), 3, '0', STR_PAD_LEFT),
            'guiche' => $this->getGuiche(),
            'numeroGuiche' => $this->getNumeroGuiche()
        );
    }

}
