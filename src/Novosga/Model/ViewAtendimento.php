<?php
namespace Novosga\Model;

use Novosga\Business\AtendimentoBusiness;

/**
 * Classe ViewAtendimento
 * representa a view de historico de atendimento do banco de dados
 * 
 * @Entity
 * @Table(name="view_historico_atendimentos")
 */
class ViewAtendimento extends SequencialModel {

    /** 
     * @ManyToOne(targetEntity="Unidade") 
     * @JoinColumn(name="unidade_id", referencedColumnName="id")
     */
    protected $unidade;
    /** 
     * @ManyToOne(targetEntity="Servico") 
     * @JoinColumn(name="servico_id", referencedColumnName="id")
     */
    protected $servico;
    /** 
     * @ManyToOne(targetEntity="Prioridade") 
     * @JoinColumn(name="prioridade_id", referencedColumnName="id")
     */
    protected $prioridade;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    protected $usuario;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="usuario_tri_id", referencedColumnName="id")
     */
    protected $usuarioTriagem;
    /** @Column(type="smallint", name="num_local", nullable=false) */
    protected $local;
    /** @Column(type="datetime", name="dt_cheg", length=50, nullable=false) */
    protected $dataChegada;
    /** @Column(type="datetime", name="dt_cha", length=50, nullable=true) */
    protected $dataChamada;
    /** @Column(type="datetime", name="dt_ini", length=50, nullable=true) */
    protected $dataInicio;
    /** @Column(type="datetime", name="dt_fim", length=50, nullable=true) */
    protected $dataFim;
    /** @Column(type="smallint", name="status", length=50, nullable=false) */
    protected $status;
    /** @Column(type="string", name="nm_cli", length=100, nullable=true) */
    protected $nomeCliente;
    /** @Column(type="string", name="ident_cli", length=11, nullable=true) */
    protected $documentoCliente;
    /** @Column(type="string", name="sigla_senha", length=3, nullable=false) */
    protected $siglaSenha;
    /** @Column(type="integer", name="num_senha", nullable=false) */
    protected $numeroSenha;
    /** @Column(type="integer", name="num_senha_serv", nullable=false) */
    protected $numeroSenhaServico;
    /**
     * @OneToMany(targetEntity="ViewAtendimentoCodificado", mappedBy="atendimento")
     * @var ViewAtendimento[]
     */
    protected $codificados;
    
    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade($unidade) {
        $this->unidade = $unidade;
    }

    public function getServico() {
        return $this->servico;
    }

    public function setServico($servico) {
        $this->servico = $servico;
    }

    public function getPrioridade() {
        return $this->prioridade;
    }

    public function setPrioridade($prioridade) {
        $this->prioridade = $prioridade;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function setUsuarioTriagem(Usuario $usuario) {
        $this->usuarioTriagem = $usuario;
    }

    public function getUsuarioTriagem() {
        return $this->usuarioTriagem;
    }

    public function getLocal() {
        return $this->local;
    }

    public function setLocal($local) {
        $this->local = $local;
    }

    /**
     * @return \DateTime 
     */
    public function getDataChegada() {
        return $this->dataChegada;
    }

    public function setDataChegada(\DateTime $dataChegada) {
        $this->dataChegada = $dataChegada;
    }

    /**
     * @return \DateTime 
     */
    public function getDataChamada() {
        return $this->dataChamada;
    }

    public function setDataChamada(\DateTime $dataChamada) {
        $this->dataChamada = $dataChamada;
    }

    /**
     * @return \DateTime 
     */
    public function getDataInicio() {
        return $this->dataInicio;
    }

    public function setDataInicio(\DateTime $dataInicio) {
        $this->dataInicio = $dataInicio;
    }

    /**
     * @return \DateTime 
     */
    public function getDataFim() {
        return $this->dataFim;
    }

    public function setDataFim(\DateTime $dataFim) {
        $this->dataFim = $dataFim;
    }

    public function getStatus() {
        return $this->status;
    }
    
    /**
     * Retorna o nome do status do atendimento
     * @return type
     */
    public function getNomeStatus() {
        return AtendimentoBusiness::nomeSituacao($this->getStatus());
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getNomeCliente() {
        return $this->nomeCliente;
    }

    public function setNomeCliente($nomeCliente) {
        $this->nomeCliente = $nomeCliente;
    }

    public function getDocumentoCliente() {
        return $this->documentoCliente;
    }

    public function setDocumentoCliente($documentoCliente) {
        $this->documentoCliente = $documentoCliente;
    }
    
    public function getSiglaSenha() {
        return $this->siglaSenha;
    }

    public function setSiglaSenha($siglaSenha) {
        $this->siglaSenha = $siglaSenha;
    }

    public function getNumeroSenha() {
        return $this->numeroSenha;
    }

    public function setNumeroSenha($numeroSenha) {
        $this->numeroSenha = $numeroSenha;
    }
    
    public function getNumeroSenhaServico() {
        return $this->numeroSenhaServico;
    }

    public function setNumeroSenhaServico($numeroSenhaServico) {
        $this->numeroSenhaServico = $numeroSenhaServico;
    }
    
    public function getCodificados() {
        return $this->codificados;
    }

    public function setCodificados(ViewAtendimento $codificados) {
        $this->codificados = $codificados;
    }
    
    /**
     * Retorna o tempo de espera do cliente até ser atendido.
     * A diferença entre a data de chegada até a data atual.
     * 
     * @return \DateInterval
     */
    public function getTempoEspera() {
        $now = new \DateTime();
        return $now->diff($this->getDataChegada());
    }

    /**
     * Retorna o tempo de permanência do cliente na unidade.
     * A diferença entre a data de chegada até a data de fim de atendimento.
     * 
     * @return \DateInterval
     */
    public function getTempoPermanencia() {
        if ($this->getDataFim()) {
            return $this->getDataFim()->diff($this->getDataChegada());
        }
        return new \DateInterval();
        
    }
    
    /**
     * Retorna o tempo total do atendimento.
     * A diferença entre a data de início e fim do atendimento.
     * 
     * @return \DateInterval
     */
    public function getTempoAtendimento() {
        if ($this->getDataFim()) {
            return $this->getDataFim()->diff($this->getDataInicio());
        }
        return new \DateInterval();
        
    }

}
