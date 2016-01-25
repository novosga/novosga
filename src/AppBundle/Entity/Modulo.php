<?php

namespace AppBundle\Entity;

/**
  * Classe Modulo
  * Para controle dos modulos do sistema.
  *
  */
 class Modulo extends SequencialModel
 {
     // TIPO
    const MODULO_UNIDADE = 0;
    const MODULO_GLOBAL = 1;

    /**
     * @var string
     */
    protected $chave;

    /**
     * @var string
     */
    protected $nome;

    /**
     * @var string
     */
    protected $descricao;

    /**
     * @var int
     */
    protected $tipo;

    /**
     * @var int
     */
    protected $status;

    // transient

    protected $dir;
     protected $path;
     protected $realPath;

    /**
     * Define a chave do Modulo.
     *
     * @param string $chave
     */
    public function setChave($chave)
    {
        $this->chave = $chave;
    }

    /**
     * Retorna a chave do Modulo.
     *
     * @return string
     */
    public function getChave()
    {
        return $this->chave;
    }

    /**
     * Define o nome do Modulo.
     *
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * Retorna o nome do Modulo.
     *
     * @return string
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
     * @return string
     */
    public function toString()
    {
        return 'Modulo['.$this->getChave().']';
    }

     public function jsonSerialize()
     {
         return [
            'id'        => $this->getId(),
            'nome'      => $this->getNome(),
            'chave'     => $this->getChave(),
            'descricao' => $this->getDescricao(),
            'status'    => $this->getStatus(),
            'tipo'      => $this->getTipo(),
        ];
     }
 }
