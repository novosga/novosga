<?php
namespace core\model;

use \core\model\Model;

/**
 * 
 */
class Lotacao extends Model {
	
    protected $usuario;
    protected $grupo;
    protected $cargo;

    public function __construct(Usuario $usuario, Grupo $grupo, Cargo $cargo) {
        $this->setUsuario($usuario);
        $this->setGrupo($grupo);
        $this->setCargo($cargo);
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
