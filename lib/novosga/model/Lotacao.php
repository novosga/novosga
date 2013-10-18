<?php
namespace novosga\model;

/**
 * @Entity
 * @Table(name="usu_grup_cargo")
 */
class Lotacao extends Model {
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Usuario")
     * @JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    protected $usuario;
    /** 
     * @Id
     * @ManyToOne(targetEntity="Grupo")
     * @JoinColumn(name="grupo_id", referencedColumnName="id")
     */
    protected $grupo;
    /** 
     * @ManyToOne(targetEntity="Cargo")
     * @JoinColumn(name="cargo_id", referencedColumnName="id")
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
