<?php
namespace core\model;

/**
 * @MappedSuperClass
 */
abstract class SuperClass {
    /** 
     * @Id 
     * @GeneratedValue 
     * @Column(type="integer", name="id", nullable=false) 
     */
    protected $id = 0;
    
    public function __tostring() { return "{$this->id}"; }
}

/**
 * @Entity 
 * @Table(name="child_a")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_a",type="integer")
 *      )
 * })
 */
class ChildA extends SuperClass {
    /**
     * @OneToOne(targetEntity="ChildB", inversedBy="a", fetch="EAGER")
     * @JoinColumn(name="id_b", referencedColumnName="id_b")
     */
    protected $b;
    
    public function getB() { return $this->b ; }
}

/**
 * @Entity 
 * @Table(name="child_a")
 * @AttributeOverrides({
 *      @AttributeOverride(name="id",
 *          column=@Column(name="id_a",type="integer")
 *      )
 * })
 */
class ChildB extends SuperClass {
    /** @OneToOne(targetEntity="ChildA", mappedBy="b", fetch="LAZY") */
    private $a;
    
    public function getA() { return $this->a ; }
}


$child = \core\db\DB::getEntityManager()->find("core\model\ChildA", 1);
echo 'A = ' . $child->getB() . '<br>';

$child = \core\db\DB::getEntityManager()->find("core\model\ChildB", 1);
echo 'B = ' . $child->getA() . '<br>';