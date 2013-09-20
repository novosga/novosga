<?php
namespace novosga\model\util;

use \novosga\model\Model;

/**
 * Classe Cliente
 * Contem informacoes sobre o Cliente a ser atendido
 *
 * @author dataprev
 * 
 */
class Cliente extends Model {

    private $nome;
    private $documento;
    
    public function __construct() {
    }

    /**
    * Define o nome do Cliente
    * @param String $nome
    */
    public function setNome($nome) {
        $this->nome = $nome;
    }

    /**
    * Retorna o nome do Cliente
    * @return String
    */
    public function getNome() {
        return $this->nome;
    }
	
    /**
    * Define o documento do Cliente
    * @param String $documento
    */
    public function setDocumento($documento) {
        $this->documento = $documento;
    }

    /**
    * Retorna a documento do Cliente
    * @return String
    */
    public function getDocumento() {
        return $this->documento;
    }

    /**
     * Retorna String com senha, prioridade e nome do cliente
     * @return String
     */
    public function toString() {
        $pri = ($this->senha->getPrioridade()) ? "***" : "";
        return "{$this->get_senha()->tostring()} - $pri {$this->getNome()}";
    }

}
