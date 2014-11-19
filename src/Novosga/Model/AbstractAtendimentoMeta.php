<?php
namespace Novosga\Model;

/**
 * AbstractAtendimentoMeta
 * Atendimento metadata
 * 
 * @MappedSuperClass
 */
abstract class AbstractAtendimentoMeta extends Model 
{
    
    /** 
     * @Id 
     * @Column(name="name", type="string", length=50, nullable=false)
     */
    protected $name;
    
    /** 
     * @Column(name="value", type="string", columnDefinition="text") 
     */
    protected $value;
    
    public abstract function getAtendimento();
    public abstract function setAtendimento(AbstractAtendimento $atendimento);
    
    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

}
