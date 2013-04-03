<?php
namespace core\model;

use \core\model\SequencialModel;
use \core\Security;

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
    /** @Column(type="string", name="email_usu", length=255, nullable=false) */
    protected $email;
    /** @Column(type="string", name="senha_usu", length=100, nullable=false) */
    protected $senha;
    /** @Column(type="string", name="senha_reset_token", length=100, nullable=false) */
    protected $senhaResetToken;
    /** @Column(type="datetime", name="senha_reset_expir", nullable=true) */
    protected $senhaResetExpir;
    /** @Column(type="integer", name="stat_usu", nullable=true) */
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

    /**
     * Recupera o hash da senha armazenada
     * @return string
     */
    public function getSenha() {
        return $this->senha;
    }

    /**
     * Define a senha para o usuário, encriptando ela
     * antes de salvar.
     * @param string $senha A senha em texto puro
     */
    public function setSenha($senha) {
        $this->senha = Security::passEncode($senha);
    }

    /**
     * Valida uma senha
     *
     * @throws Exception Se a senha não for válida dispara
     * mensagem apropriada para o tipo de erro.
     *
     * @param  string $senha
     * @param  string $confirmacao
     * @return boolean true se a senha for válida
     */
    public function validaSenha($senha, $confirmacao) {
        if ($senha != $confirmacao) {
            throw new Exception(_('A confirmação de senha não confere com a senha.'));
        }

        if (function_exists('mb_strlen')) {
            if (mb_strlen($senha) >= 6) {
                return true;
            }
        } else if (strlen($senha) >= 6) {
            return true;
        }

        throw new Exception(_('A senha deve possuir no mínimo 6 caracteres.'));
    }

    public function getSenhaResetToken() {
        return $this->senhaResetToken;
    }

    public function setSenhaResetToken() {
        $this->senhaResetToken = base64_encode(Security::hash($email . time()));
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
