<?php

namespace Novosga\Entity;

use DateTime;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;

/**
 * Usuario
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Usuario extends SequencialModel implements AdvancedUserInterface, EncoderAwareInterface, \Serializable
{

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string
     */
    protected $sobrenome;

    /**
     * @var string
     */
    protected $senha;

    /**
     * @var bool
     */
    protected $status;

    /**
     * @var DateTime
     */
    protected $ultimoAcesso;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var Lotacao[]
     */
    protected $lotacoes;

    /**
     * @var string
     */
    protected $algorithm;

    /**
     * @var string
     */
    protected $salt;

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
        $this->status = $status;
    }

    public function getLotacoes()
    {
        return $this->lotacoes;
    }
    
    public function setSalt($salt) 
    {
        $this->salt = $salt;
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

    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return !!$this->getStatus();
    }

    public function eraseCredentials()
    {
    }

    public function getPassword()
    {
        return $this->getSenha();
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->getLogin();
    }

    public function getEncoderName()
    {
        return $this->algorithm;
    }

    public function serialize()
    {
        return serialize([
            $this->id,
            $this->login,
            $this->nome
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->login,
            $this->nome
        ) = unserialize($serialized);
    }

    public function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
            'login'     => $this->getLogin(),
            'nome'      => $this->getNome(),
            'sobrenome' => $this->getSobrenome(),
            'status'    => $this->getStatus()
        ];
    }
}
