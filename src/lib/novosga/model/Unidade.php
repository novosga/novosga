<?php
namespace novosga\model;

/**
 * Unidade de atendimento
 * 
 * @Entity
 * @Table(name="unidades")
 */
class Unidade extends SequencialModel {

    /** @Column(type="string", name="codigo", length=10, nullable=false) */
    protected $codigo;
    /** @Column(type="string", name="nome", length=50, nullable=false) */
    protected $nome;
    /** @Column(type="integer", name="status", nullable=false) */
    protected $status;
    /**
     * @OneToOne(targetEntity="Grupo", fetch="EAGER")
     * @JoinColumn(name="grupo_id", referencedColumnName="id")
     */
    protected $grupo;
    
    /** @Column(type="integer", name="stat_imp", nullable=false) */
    protected $statusImpressao;
    /** @Column(type="string", name="msg_imp", length=100) */
    protected $mensagemImpressao;
	

    public function __construct() {
    }

    public function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getNome() {
            return $this->nome;
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setGrupo(Grupo $grupo) {
        $this->grupo = $grupo;
    }

    /**
     * @return Grupo
     */
    public function getGrupo() {
        return $this->grupo;
    }
    
    public function getStatusImpressao() {
        return $this->statusImpressao;
    }

    public function setStatusImpressao($statusImpressao) {
        $this->statusImpressao = $statusImpressao;
    }

    public function getMensagemImpressao() {
        return $this->mensagemImpressao;
    }

    public function setMensagemImpressao($mensagemImpressao) {
        $this->mensagemImpressao = $mensagemImpressao;
    }
    
    public function toString() {
        return $this->getNome();
    }

}
