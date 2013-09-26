<?php
namespace novosga\model;

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
    /** 
     * @ ManyToOne(targetEntity="TreeModel")
     * @ JoinColumn(name="parent_id", referencedColumnName="id", nullable=false) 
     */
    protected $parent;
    /** @Column(type="integer", name="nivel", nullable=false) */
    protected $level;

    
    public function getLeft() {
        return $this->left;
    }

    public function setLeft($left) {
        $this->left = $left;
    }
    
    public function getRight() {
        return $this->right;
    }

    public function setRight($right) {
        $this->right = $right;
    }
    
    public function getLevel() {
        return $this->level;
    }

    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * 
     * @return TreeModel
     */
    public function getParent() {
        if (!$this->isRoot() && $this->parent == null) {
            $em = \novosga\db\DB::getEntityManager();
            $query = $em->createQuery("
                SELECT 
                    parent
                FROM 
                    " . get_class($this) . " parent
                WHERE 
                    parent.left < :left AND 
                    parent.right > :right
                ORDER BY 
                    parent.left DESC
            ");
            $query->setParameter('left', $this->getLeft());
            $query->setParameter('right', $this->getRight());
            $query->setMaxResults(1);
            $this->parent = $query->getSingleResult();
        }
        return $this->parent;
    }

    public function setParent(TreeModel $parent) {
        $this->parent = $parent;
    }
    
    /**
     * Retorna se o model é a raíz da árvore
     * @return boolean
     */
    public function isRoot() {
        return $this->left == 1;
    }
    
    /**
     * Retorna se o model é uma folha da árvore
     * @return boolean
     */
    public function isLeaf() {
        return $this->right == $this->left + 1;
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
