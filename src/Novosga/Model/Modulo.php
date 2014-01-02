<?php
namespace Novosga\Model;

/**
 * Classe Modulo
 * Para controle dos modulos do sistema
 * 
 * @Entity
 * @Table(name="modulos")
 */
 class Modulo extends SequencialModel {

    // TIPO
    const MODULO_UNIDADE = 0;
    const MODULO_GLOBAL = 1;

    /** @Column(type="string", name="chave", length=50, nullable=false, unique=true) */
    protected $chave;
    /** @Column(type="string", name="nome", length=25, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="autor", length=25, nullable=false) */
    protected $autor;
    /** @Column(type="string", name="descricao", length=100, nullable=false) */
    protected $descricao;
    /** @Column(type="smallint", name="tipo", nullable=false) */
    protected $tipo;
    /** @Column(type="smallint", name="status", nullable=false) */
    protected $status;
    
    // transient
    
    protected $dir;
    protected $path;
    protected $realPath;

	
    /**
     * Define a chave do Modulo
     * @param String $chave
     */
    public function setChave($chave) {
        $this->chave = $chave;
    }

    /**
     * Retorna a chave do Modulo
     * @return String
     */
    public function getChave() {
        return $this->chave;
    }

    /**
     * Define o nome do Modulo
     * @param String $nome
     */
    public function setNome($nome) {
        $this->nome = $nome;
    }

    /**
     * Retorna o nome do Modulo
     * @return String
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * Define o autor do Modulo
     * @param String $autor
     */
    public function setAutor($autor) {
        $this->autor = $autor;
    }

    /**
     * Retorna o autor do Modulo
     * @return String
     */
    public function getAutor() {
        return $this->autor;
    }
    
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    public function getDescricao() {
        return $this->descricao;
    }

    /**
     * Retorna o tipo do modulo, se e global ou local (unidade)
     * @return type
     */
    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function isGlobal() {
        return $this->tipo == Modulo::MODULO_GLOBAL;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Retorna o diretorio do Modulo
     * @return String
     */
    public function getDir() {
        if (!$this->dir) {
            $this->dir = self::dir($this->chave);
        }
        return $this->dir;
    }
    
    public function getPath() {
        if (!$this->path) {
            $this->path = self::path($this->chave);
        }
        return $this->path;
    }

    public function getRealPath() {
        if (!$this->realPath) {
            $this->realPath = self::realPath($this->chave);
        }
        return $this->realPath;
    }
    
    public static function dir($chave) {
        return join(DS, explode('.', $chave));
    }
    
    public static function path($chave) {
        return MODULES_DIR . DS . self::dir($chave);
    }
    
    public static function realPath($chave) {
        return MODULES_PATH . DS . self::dir($chave);
    }
	
    /**
     * Retorna String com Chave do módulo
     * @return String
     */
    public function toString() {
        return "Modulo[". $this->getChave() ."]";
    }

}
