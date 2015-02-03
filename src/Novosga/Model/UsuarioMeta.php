<?php
namespace Novosga\Model;

/**
 * Usuario metadata
 * 
 * @Entity
 * @Table(name="usu_meta")
 */
abstract class UsuarioMeta extends Model implements \JsonSerializable
{
    
    /** 
     * @Id 
     * @Column(name="name", type="string", length=50, nullable=false)
     */
    protected $name;
    
    /** 
     * @Id 
     * @ManyToOne(targetEntity="Usuario")
     * @JoinColumn(name="usuario_id", referencedColumnName="id")
     * @var Usuario
     */
    protected $usuario;
    
    /** 
     * @Column(name="value", type="string", columnDefinition="text") 
     */
    protected $value;
    
    public function getName() {
        return $this->name;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function getValue() {
        return $this->value;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setUsuario(Usuario $usuario) {
        $this->usuario = $usuario;
        return $this;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
    
    public function jsonSerialize() {
        return array(
            'name' => $this->getName(),
            'value' => $this->getValue()
        );
    }

}
