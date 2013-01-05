<?php
namespace core\model;

use \core\model\SequencialModel;

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
    /** @Column(type="integer", name="session_id", nullable=true) */
    protected $sessionId;
    /** 
     * @OneToMany(targetEntity="Lotacao", mappedBy="usuario")
     */
    protected $lotacoes;
    
    // transient - os campos abaixo dependem da unidade atual
    protected $grupos;
    protected $servicos;
    
    protected $guiche;
    protected $unidade;
    protected $lotacao;
    protected $ativo = false;

    public function __construct() {
    }
    
    public function setLogin($mat) {
        $this->login = $mat;
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
    
    /**
     * Retorna o número do guiche para atendimento na unidade atual
     * @return type
     */
    public function getGuiche() {
        return $this->guiche;
    }

    public function setGuiche($guiche) {
        $guiche = (int) $guiche;
        if ($guiche > 0) {
            $this->guiche = $guiche;
        } else {
            throw new Exception(_('Erro ao definir guiche do Usuário. Deve ser um número maior que zero.'));
        }
    }

    public function getGrupos() {
        return $this->grupos;
    }

    public function setGrupos($grupos) {
        $this->grupos = $grupos;
    }

    /**
     * @return Unidade
     */
    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade(Unidade $unidade = null) {
        $this->unidade = $unidade;
        if ($unidade != null) {
//            $db = DB::getAdapter();
//            $this->setLotacao($db->getLotacao_valida($this->getId(), $unidade->getGrupo()->getId()));
//            $this->setServicos($db->get_usuario_servicos_unidade($this->getId(), $unidade->getId()));
        } else {
            $this->setServicos(array());
        }
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
    
    public function getSessionId() {
        return $this->sessionId;
    }

    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }
    
    public function isAtivo() {
        return ($this->ativo == true);
    }

    public function setAtivo($ativo) {
        $this->ativo = ($ativo == true);
    }
    
    public function tostring() {
        return $this->getLogin();
    }

}
