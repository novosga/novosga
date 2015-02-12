<?php
namespace Novosga\Model;

/**
 * AbstractAtendimentoMeta
 * Atendimento metadata
 * 
 * @MappedSuperClass
 */
abstract class AbstractAtendimentoMeta extends Metadata 
{
    
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
    
    public function getEntity() {
        return $this->getAtendimento();
    }

    public function setEntity($entity) {
        $this->setAtendimento($entity);
    }


}
