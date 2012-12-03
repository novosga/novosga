<?php
namespace core\model;

use \core\model\TreeModel;


/**
 * Classe Cargo
 * Um cargo define permissões de acesso a módulos do sistema
 * 
 * @Entity
 * @Table(name="cargos_aninhados")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_cargo",type="integer")
 *      )
 * })
 */
class Cargo extends TreeModel {

    /** @Column(type="string", name="nm_cargo", length=40, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="desc_cargo", length=150, nullable=false) */
    protected $descricao;
    
    protected $permissoes;

    /**
     * Define o nome do Cargo
     * @param String $nome
     */
    public function setNome($nome) {
        $this->nome = $nome;
    }
	
    /**
     * Retorna a descrição do Cargo
     * @return int
     */
    public function getDescricao() {
        return $this->descricao;
    }

    /**
     * Define a descrição do Cargo
     * @param String $nome
     */
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    /**
     * Retorna o nome do Cargo
     * @return String
     */
    public function getNome() {
        return $this->nome;
    }

    /**
     * Adicinoa permissão para acessar módulo
     * @param $pm
     * @return none
     */
    public function addPermissao(PermissaoModulo $pm) {
        $this->permissoes[] = $pm;	
    }
	
    /**
     * Modifica permissões
     * @return $permissoes array
     */
    public function getPermissoes() {
        // lazy loading (carrega sob demanda)
        if (!$this->permissoes) {
            //$this->permissoes = DB::getAdapter()->get_permissoes_cargo($this->getId());
        }
        return $this->permissoes;	
    }
	
    /**
     * Verfica se tem permissão para acessar módulo
     * @param $modulo
     * @return bool
     */
    public function hasPermissao($modulo) {
        if ($modulo instanceof Modulo) {
            $id_mod = $modulo->getId();
        }
        else {
            $id_mod = (int) $modulo;
        }
        foreach ($this->get_permissoes() as $pc) {
            if ($pc->get_modulo()->getId() == $id_mod) {
                return true;
            }
        }
        return false;
    }
    
    public function toString() {
        return $this->nome;
    }
    
}
