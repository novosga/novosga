<?php
namespace core\model;

use \core\model\TreeModel;
use \core\model\Unidade;

/**
 * Classe Grupo
 * Atraves do grupo e definido o acesso do Usuario
 * 
 * @Entity
 * @Table(name="grupos_aninhados")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_grupo",type="integer")
 *      )
 * })
 */
class Grupo extends TreeModel {

    /** @Column(type="string", name="nm_grupo", length=40, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="desc_grupo", length=150, nullable=false) */
    protected $descricao;
    // XXX: retirado relacionamento bidirecional devido a bug do dblib/mssql no linux (multiplas consultas)
    /** @ OneToOne(targetEntity="Unidade", mappedBy="grupo", fetch="LAZY") */
    protected $unidade;


    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getNome() {
        return $this->nome;
    }
    
    public function getDescricao() {
        return $this->descricao;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade($unidade) {
        $this->unidade = $unidade;
    }

    public function toString() {
        return $this->nome;
    }
    
}
