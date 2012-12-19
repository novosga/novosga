<?php
namespace core\model;

use \core\model\Modulo;

/**
 * @Entity
 * @Table(name="cargos_mod_perm")
 */
class Permissao extends Model {
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Modulo")
     * @JoinColumn(name="id_mod", referencedColumnName="id_mod")
     */
    protected $modulo;
    /** 
     * @Id
     * @ManyToOne(targetEntity="Cargo")
     * @JoinColumn(name="id_cargo", referencedColumnName="id_cargo")
     */
    protected $cargo;
    /** @Column(type="integer", name="permissao", nullable=false) */
    protected $permissao;
	
    public function __construct() {
    }

    /**
     * Define o modulo ao qual a permissão se refere
     * @param Modulo $modulo 
     */
    public function setModulo(Modulo $modulo) {
        $this->modulo = $modulo;
    }

    /**
     * Retorna o modulo ao qual esta permissão se refere
     * @return Modulo 
     */
    public function getModulo() {
        return $this->modulo;
    }
    public function getCargo() {
        return $this->cargo;
    }

    public function setCargo($cargo) {
        $this->cargo = $cargo;
    }

    public function getPermissao() {
        return $this->permissao;
    }

    public function setPermissao($permissao) {
        $this->permissao = $permissao;
    }

}
