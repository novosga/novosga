<?php
namespace core\model;

/**
 * @Entity
 * @Table(name="usu_serv")
 */
class ServicoUsuario extends Model {
    
    // (bug ao tentar mapear ServicoUnidade: composite primary key as part of the primary key of another entity)
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Servico")
     * @JoinColumn(name="id_serv", referencedColumnName="id_serv")
     */
    protected $servico;
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Unidade")
     * @JoinColumn(name="id_uni", referencedColumnName="id_uni")
     */
    protected $unidade;
    
    /** 
     * @Id
     * @ManyToOne(targetEntity="Usuario")
     * @JoinColumn(name="id_usu", referencedColumnName="id_usu")
     */
    protected $usuario;
	
    public function __construct() {
    }
    
    public function getServico() {
        return $this->servico;
    }

    public function setServico($servico) {
        $this->servico = $servico;
    }

    public function getUnidade() {
        return $this->unidade;
    }

    public function setUnidade($unidade) {
        $this->unidade = $unidade;
    }

    /**
     * 
     * @return Usuario
     */
    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

}
