<?php
namespace core\model;

use \core\model\Modulo;

/**
 * 
 */
class PermissaoModulo extends Model {
    
    protected $modulo;
	
    public function __construct(Modulo $modulo) {
        $this->setModulo($modulo);
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
    
}
