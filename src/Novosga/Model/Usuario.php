<?php

namespace Novosga\Model;

/**
 * @Entity
 * @Table(name="usuarios")
 */
class Usuario extends SequencialModel
{
    /**
     * @Column(type="string", name="login", length=20, nullable=false, unique=true)
     */
    protected $login;

    /**
     * @Column(type="string", name="nome", length=20, nullable=false)
     */
    protected $nome;

    /**
     * @Column(type="string", name="sobrenome", length=100, nullable=false)
     */
    protected $sobrenome;

    /**
     * @Column(type="string", name="senha", length=60, nullable=false)
     */
    protected $senha;

    /**
     * @Column(type="smallint", name="status", nullable=false)
     */
    protected $status;

    /**
     * @Column(type="datetime", name="ult_acesso", nullable=true)
     */
    protected $ultimoAcesso;

    /**
     * @Column(type="string", name="session_id", length=50, nullable=true)
     */
    protected $sessionId;

    /**
     * @OneToMany(targetEntity="Lotacao", mappedBy="usuario")
     */
    protected $lotacoes;

    // transient - os campos abaixo dependem da unidade atual
    protected $grupos;
    protected $servicos;

    public function __construct()
    {
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setSobrenome($sobrenome)
    {
        $this->sobrenome = $sobrenome;
    }

    public function getSobrenome()
    {
        return $this->sobrenome;
    }

    /**
     * Retorna o nome completo do usuario (nome + sobrenome).
     *
     * @return string
     */
    public function getNomeCompleto()
    {
        return $this->nome.' '.$this->sobrenome;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function getGrupos()
    {
        return $this->grupos;
    }

    public function setGrupos($grupos)
    {
        $this->grupos = $grupos;
    }

    public function setServicos(array $servicos)
    {
        $this->servicos = $servicos;
    }

    /**
     * Retorna os servicos do usuario na unidade atual.
     *
     * @return type
     */
    public function getServicos()
    {
        return $this->servicos;
    }

    public function setStatus($status)
    {
        if (is_int($status)) {
            $this->status = $status;
        } else {
            throw new Exception(_('Erro ao definir status do Atendente, deve ser um inteiro.'));
        }
    }

    public function getLotacoes()
    {
        return $this->lotacoes;
    }

    public function setLotacoes($lotacoes)
    {
        $this->lotacoes = $lotacoes;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getUltimoAcesso()
    {
        return $this->ultimoAcesso;
    }

    public function setUltimoAcesso($ultimoAcesso)
    {
        $this->ultimoAcesso = $ultimoAcesso;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function toString()
    {
        return $this->getLogin();
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'login' => $this->getLogin(),
            'nome' => $this->getNome(),
            'sobrenome' => $this->getSobrenome(),
            'senha' => $this->getSenha(),
        );
    }
}
