<?php
namespace novosga\model;

/**
 * 
 * @Entity
 * @Table(name="locais")
 */
class Local extends SequencialModel {

    /** @Column(type="string", name="nm_loc", length=30, nullable=false) */
    protected $nome;

    public function __construct() {
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getNome() {
        return $this->nome;
    }

}
