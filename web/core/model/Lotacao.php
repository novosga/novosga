<?php
namespace core\model;

use \core\model\Model;

/**
 * @Entity
 * @Table(name="usu_grup_cargo")
 */
class Lotacao extends Model {
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Usuario")
     * @JoinColumn(name="id_usu", referencedColumnName="id_usu")
     */
    protected $usuario;
    /** 
     * @Id
     * @ManyToOne(targetEntity="Grupo")
     * @JoinColumn(name="id_grupo", referencedColumnName="id_grupo")
     */
    protected $grupo;
    /** 
     * @ManyToOne(targetEntity="Cargo")
     * @JoinColumn(name="id_cargo", referencedColumnName="id_cargo")
     */
    protected $cargo;

    public function __construct() {
    }
	
    /**
     * Modifica usuario
     * @param $usuario
     * @return none
     */
    public function setUsuario(Usuario $usuario) {
        $this->usuario = $usuario;
    }
	
    /**
     * Retorna objeto usuario
     * @return Usuario $usuario
     */
    public function getUsuario() {
        return $this->usuario;
    }
	
    /**
     * Modifica grupo
     * @param $grupo
     * @return none
     */
    public function setGrupo(Grupo $grupo) {
        $this->grupo = $grupo;
    }
	
    /**
     * Retorna objeto Grupo
     * @return Grupo $grupo
     */
    public function getGrupo() {
        return $this->grupo;
    }
	
    /**
     * Modifica cargo
     * @param $cargo
     * @return none
     */
    public function setCargo(Cargo $cargo) {
        $this->cargo = $cargo;
    }
	
    /**
     * Retorna objeto Cargo
     * @return Cargo $cargo
     */
    public function getCargo() {
        return $this->cargo;
    }
    
}
