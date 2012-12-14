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
    /** 
     * @OneToMany(targetEntity="PermissaoModulo", mappedBy="cargo")
     */
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
     * Retorna as permissões do cargo
     * @return $permissoes array
     */
    public function getPermissoes() {
        return $this->permissoes;
    }
	
    /**
     * Verfica se tem permissão para acessar módulo
     * @param $modulo
     * @return bool
     */
    public function hasPermissao(Modulo $modulo) {
        foreach ($this->getPermissoes() as $permissao) {
            if ($permissao->getModulo()->getId() == $modulo->getId()) {
                return true;
            }
        }
        return false;
    }
    
    public function toString() {
        return $this->nome;
    }
    
}
