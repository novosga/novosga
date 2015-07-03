<?php

namespace Novosga\Model;

/**
  * Classe Modulo
  * Para controle dos modulos do sistema.
  *
  * @Entity
  * @Table(name="modulos")
  */
 class Modulo extends SequencialModel
 {
     // TIPO
    const MODULO_UNIDADE = 0;
     const MODULO_GLOBAL = 1;

    /**
     * @Column(type="string", name="chave", length=50, nullable=false, unique=true)
     */
    protected $chave;

    /**
     * @Column(type="string", name="nome", length=25, nullable=false)
     */
    protected $nome;

    /**
     * @Column(type="string", name="descricao", length=100, nullable=false)
     */
    protected $descricao;

    /**
     * @Column(type="smallint", name="tipo", nullable=false)
     */
    protected $tipo;

    /**
     * @Column(type="smallint", name="status", nullable=false)
     */
    protected $status;

    // transient

    protected $dir;
     protected $path;
     protected $realPath;

    /**
     * Define a chave do Modulo.
     *
     * @param String $chave
     */
    public function setChave($chave)
    {
        $this->chave = $chave;
    }

    /**
     * Retorna a chave do Modulo.
     *
     * @return String
     */
    public function getChave()
    {
        return $this->chave;
    }

    /**
     * Define o nome do Modulo.
     *
     * @param String $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * Retorna o nome do Modulo.
     *
     * @return String
     */
    public function getNome()
    {
        return $this->nome;
    }

     public function setDescricao($descricao)
     {
         $this->descricao = $descricao;
     }

     public function getDescricao()
     {
         return $this->descricao;
     }

    /**
     * Retorna o tipo do modulo, se e global ou local (unidade).
     *
     * @return type
     */
    public function getTipo()
    {
        return $this->tipo;
    }

     public function setTipo($tipo)
     {
         $this->tipo = $tipo;
     }

     public function isGlobal()
     {
         return $this->tipo == self::MODULO_GLOBAL;
     }

     public function getStatus()
     {
         return $this->status;
     }

     public function setStatus($status)
     {
         $this->status = $status;
     }

     public function getRealPath()
     {
         if (!$this->realPath) {
             $this->realPath = self::realPath($this->chave);
         }

         return $this->realPath;
     }

     public static function realPath($chave)
     {
         return MODULES_PATH.DS.implode(DS, explode('.', $chave));
     }

    /**
     * @return String
     */
    public function toString()
    {
        return 'Modulo['.$this->getChave().']';
    }

     public function jsonSerialize()
     {
         return array(
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'chave' => $this->getChave(),
            'descricao' => $this->getDescricao(),
            'status' => $this->getStatus(),
            'tipo' => $this->getTipo(),
        );
     }
 }
