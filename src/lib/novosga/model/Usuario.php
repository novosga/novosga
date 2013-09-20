<?php
namespace novosga\model;

/**
 * 
 * @Entity
 * @Table(name="usuarios")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_usu",type="integer")
 *      )
 * })
 */
class Usuario extends SequencialModel {
    
    /** @Column(type="string", name="login_usu", length=20, nullable=false) */
    protected $login;
    /** @Column(type="string", name="nm_usu", length=20, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="ult_nm_usu", length=100, nullable=false) */
    protected $sobrenome;
    /** @Column(type="string", name="senha_usu", length=40, nullable=false) */
    protected $senha;
    /** @Column(type="integer", name="stat_usu", nullable=false) */
    protected $status;
    /** @Column(type="string", name="ult_acesso", nullable=true) */
    protected $ultimoAcesso;
    /** @Column(type="integer", name="session_id", nullable=true) */
    protected $sessionId;
    /** 
     * @OneToMany(targetEntity="Lotacao", mappedBy="usuario")
     */
    protected $lotacoes;
    
    // transient - os campos abaixo dependem da unidade atual
    protected $grupos;
    protected $servicos;
    
    public function __construct() {
    }
    
    public function setLogin($login) {
        $this->login = $login;
    }
    
    public function getLogin() {
        return $this->login;
    }
    
    public function setNome($nome) {
        $this->nome = $nome;
    }
    
    public function getNome() {
        return $this->nome;
    }
    
    public function setSobrenome($sobrenome) {
        $this->sobrenome = $sobrenome;
    }
    
    public function getSobrenome() {
        return $this->sobrenome;
    }
    
    /**
     * Retorna o nome completo do usuario (nome + sobrenome)
     * @return string
     */
    public function getNomeCompleto() {
        return $this->nome . ' ' . $this->sobrenome;
    }
    
    public function getSenha() {
        return $this->senha;
    }

    public function setSenha($senha) {
        $this->senha = $senha;
    }
    
    public function getGrupos() {
        return $this->grupos;
    }

    public function setGrupos($grupos) {
        $this->grupos = $grupos;
    }
    
    public function setServicos(array $servicos) {
        $this->servicos = $servicos;
    }

    /**
     * Retorna os servicos do usuario na unidade atual
     * @return type
     */
    public function getServicos() {
        return $this->servicos;
    }
    
    public function setStatus($status) {
        if (is_int($status)) {
            $this->status = $status;
        } else {
            throw new Exception(_('Erro ao definir status do Atendente, deve ser um inteiro.'));
        }
    }
       
    public function getLotacoes() {
        return $this->lotacoes;
    }

    public function setLotacoes($lotacoes) {
        $this->lotacoes = $lotacoes;
    }

    public function getStatus() {
        return $this->status;
    }
    
    public function getUltimoAcesso() {
        return $this->ultimoAcesso;
    }

    public function setUltimoAcesso($ultimoAcesso) {
        $this->ultimoAcesso = $ultimoAcesso;
    }
        
    public function getSessionId() {
        return $this->sessionId;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }
    
    public function toString() {
        return $this->getLogin();
    }

}
