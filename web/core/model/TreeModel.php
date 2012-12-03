<?php
namespace core\model;

use \core\model\SequencialModel;

/**
 * Tree Model
 * 
 * @MappedSuperClass
 */
abstract class TreeModel extends SequencialModel {

    /** @Column(type="integer", name="esquerda", nullable=false) */
    protected $left = 1;
    /** @Column(type="integer", name="direita", nullable=false) */
    protected $right = 2;
    
    // transient
    
    private $parent;
    private $root = true;
    
    public function getLeft() {
        return $this->left;
    }

    public function setLeft($left) {
        $this->left = $left;
        $this->root = $left == 1;
    }
    
    public function getRight() {
        return $this->right;
    }

    public function setRight($right) {
        $this->right = $right;
    }

    /**
     * 
     * @return TreeModel
     */
    public function getParent() {
        if (!$this->isRoot() && $this->parent == null) {
            $em = \core\db\DB::getEntityManager();
            $query = $em->createQuery("
                SELECT 
                    pai
                FROM 
                    " . get_class($this) . " AS parent
                LEFT JOIN
                    " . get_class($this) . " AS child
                    ON 
                        child.left > parent.left AND 
                        child.right < parent.right
                WHERE 
                    child.id = :id 
                ORDER BY 
                    parent.left DESC
            ");
            $query->setParameter('id', $this->getId());
            $this->parent = $query->getFirstResult();
            
        }
        return $this->parent;
    }

    public function setParent(TreeModel $parent) {
        $this->parent = $parent;
    }
    
    /**
     * Retorna se o model é a raiz da arvore
     * @return boolean
     */
    public function isRoot() {
        return $this->root;
    }

    /**
     * Retorna se o model e filho do informado via parametro
     * @param TreeModel $parent
     * @return boolean
     */
    public function isChild(TreeModel $parent) {
        return $this->left > $parent->getLeft() && $this->right < $parent->getRight();
    }
    
}
