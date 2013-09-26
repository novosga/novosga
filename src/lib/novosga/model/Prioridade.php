<?php
namespace novosga\model;

/**
 * 
 * @Entity
 * @Table(name="prioridades")
 */
class Prioridade extends SequencialModel {

    /** @Column(type="string", name="nm_pri", length=30, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="desc_pri", length=100, nullable=false) */
    protected $descricao;
    /** @Column(type="integer", name="peso_pri", nullable=false) */
    protected $peso;
    /** @Column(type="integer", name="stat_pri", nullable=false) */
    protected $status;

    public function __construct() {
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setDescricao($desc) {
        $this->descricao = $desc;
    }

    public function getDescricao() {
        return $this->descricao;
    }

    public function setPeso($peso) {
        if (is_int($peso) && $peso >= 0) {
            $this->peso = $peso;
        } else {
            throw new Exception(_('O peso da prioridade deve ser um inteiro positivo'));
        }
    }

    public function getPeso() {
        return $this->peso;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function toString() {
        return $this->getNome();
    }

}
