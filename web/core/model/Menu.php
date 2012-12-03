<?php
namespace core\model;

use \core\model\SequencialModel;

/**
 * 
 * @Entity
 * @Table(name="menus")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_menu",type="integer")
 *      )
 * })
 */ 
 class Menu extends SequencialModel {
 	
    /** @Column(type="string", name="nm_menu", length=50, nullable=false) */
    protected $nome;
    /** @Column(type="string", name="lnk_menu", length=150, nullable=false) */
    protected $link;
    /** @Column(type="string", name="desc_menu", length=100, nullable=false) */
    protected $descricao;
    /** @Column(type="integer", name="ord_menu", nullable=false) */
    protected $ordem;

    
    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }
    
    public function getLink() {
        return $this->link;
    }
    
    public function setLink($link) {
        $this->link = $link;
    }
    
    public function getDescricao() {
        return $this->descricao;
    }
    
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }
    
    public function getOrdem() {
        return $this->ordem;
    }
    
    public function setOrdem($ordem) {
        if (is_int($ordem)) {
            $this->ordem = $ordem;
        } else {
            throw new Exception(_('A ordem deve ser inteiro'));
        }
    }
    
}
