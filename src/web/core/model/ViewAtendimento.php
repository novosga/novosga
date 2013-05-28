<?php
namespace core\model;

/**
 * Classe ViewAtendimento
 * representa a view de historico de atendimento do banco de dados
 * 
 * @Entity
 * @Table(name="view_historico_atendimentos")
 */
class ViewAtendimento extends Model {

    /**
     * @Id 
     * @Column(type="integer", name="id_atend", nullable=false) 
     */
    protected $id;
    /** 
     * @ManyToOne(targetEntity="Unidade") 
     * @JoinColumn(name="id_uni", referencedColumnName="id_uni")
     */
    protected $unidade;
    /** 
     * @ManyToOne(targetEntity="Servico") 
     * @JoinColumn(name="id_serv", referencedColumnName="id_serv")
     */
    protected $servico;
    /** 
     * @ManyToOne(targetEntity="Prioridade") 
     * @JoinColumn(name="id_pri", referencedColumnName="id_pri")
     */
    protected $prioridade;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="id_usu", referencedColumnName="id_usu")
     */
    protected $usuario;
    /** 
     * @ManyToOne(targetEntity="Usuario") 
     * @JoinColumn(name="id_usu_tri", referencedColumnName="id_usu")
     */
    protected $usuarioTriagem;
    /** @Column(type="integer", name="num_guiche", nullable=false) */
    protected $guiche;
    /** @Column(type="string", name="dt_cheg", length=50, nullable=false) */
    protected $dataChegada;
    /** @Column(type="string", name="dt_cha", length=50, nullable=true) */
    protected $dataChamada;
    /** @Column(type="string", name="dt_ini", length=50, nullable=true) */
    protected $dataInicio;
    /** @Column(type="string", name="dt_fim", length=50, nullable=true) */
    protected $dataFim;
    /** @Column(type="integer", name="id_stat", length=50, nullable=false) */
    protected $status;
    /** @Column(type="string", name="nm_cli", length=100, nullable=true) */
    protected $nomeCliente;
    /** @Column(type="string", name="ident_cli", length=11, nullable=true) */
    protected $documentoCliente;
    /** @Column(type="string", name="sigla_senha", length=1, nullable=false) */
    protected $siglaSenha;
    /** @Column(type="integer", name="num_senha", nullable=false) */
    protected $numeroSenha;
    /** @Column(type="integer", name="num_senha_serv", nullable=false) */
    protected $numeroSenhaServico;
    
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

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

    public function getGuiche() {
        return $this->guiche;
    }

    public function setGuiche($guiche) {
        $this->guiche = $guiche;
    }

    public function getDataChegada() {
        return $this->dataChegada;
    }

    public function setDataChegada($dataChegada) {
        $this->dataChegada = $dataChegada;
    }

    public function getDataChamada() {
        return $this->dataChamada;
    }

    public function setDataChamada($dataChamada) {
        $this->dataChamada = $dataChamada;
    }

    public function getDataInicio() {
        return $this->dataInicio;
    }

    public function setDataInicio($dataInicio) {
        $this->dataInicio = $dataInicio;
    }

    public function getDataFim() {
        return $this->dataFim;
    }

    public function setDataFim($dataFim) {
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
        $arr = Atendimento::situacoes();
        return $arr[$this->getStatus()];
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

}
