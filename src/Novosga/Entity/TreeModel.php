<?php

namespace Novosga\Entity;

/**
 * Tree Model.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class TreeModel extends SequencialModel
{
    /**
     * @var int
     */
    protected $left = 1;

    /**
     * @var int
     */
    protected $right = 2;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var TreeModel
     */
    protected $parent;

    public function getLeft()
    {
        return $this->left;
    }

    public function setLeft($left)
    {
        $this->left = $left;
    }

    public function getRight()
    {
        return $this->right;
    }

    public function setRight($right)
    {
        $this->right = $right;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(TreeModel $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Retorna se o model é a raíz da árvore.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->left == 1;
    }

    /**
     * Retorna se o model é uma folha da árvore.
     *
     * @return bool
     */
    public function isLeaf()
    {
        return $this->right == $this->left + 1;
    }

    /**
     * Retorna se o model e filho do informado via parametro.
     *
     * @param TreeModel $parent
     *
     * @return bool
     */
    public function isChild(TreeModel $parent)
    {
        return $this->left > $parent->getLeft() && $this->right < $parent->getRight();
    }

    public function jsonSerialize()
    {
        $arr = parent::jsonSerialize();

        return array_merge($arr, [
            'left' => $this->getLeft(),
            'right' => $this->getRight(),
            'level' => $this->getLevel(),
            'parent' => $this->getParent() ? $this->getParent()->getId() : null,
        ]);
    }
}
