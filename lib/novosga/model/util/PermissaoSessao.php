<?php
namespace novosga\model\util;

use \novosga\db\DB;
use \novosga\model\Permissao;

/**
 * Usuario utilizado para salvar na sessao. Assim evitar de salvar
 * as entidades do Doctrine.
 */
class PermissaoSessao {
    
    protected $usuarioId;
    protected $modulo;
    protected $moduloId;
    protected $cargo;
    protected $cargoId;
    private $wrapped;
    
    public function __construct($usuarioId, Permissao $permissao) {
        $this->usuarioId = $usuarioId;
        $this->modulo = $permissao->getModulo();
        $this->moduloId = $permissao->getModulo()->getId();
        $this->cargo = $permissao->getCargo();
        $this->cargoId = $permissao->getCargo()->getId();
    }
    
    public function getModuloId() {
        return $this->moduloId;
    }

    public function getCargoId() {
        return $this->cargoId;
    }

    /**
     * 
     * @return novosga\model\Usuario
     */
    public function getWrapped() {
        if (!$this->wrapped) {
            $query = DB::getEntityManager()->createQuery("
                SELECT e FROM novosga\model\Permissao e WHERE e.modulo = :modulo AND e.cargo = :cargo
            ");
            $query->setParameter('modulo', $this->moduloId);
            $query->setParameter('cargo', $this->cargoId);
            $this->wrapped = $query->getSingleResult();
        }
        return $this->wrapped;
    }
    
    public function __sleep() {
        return array('usuarioId', 'moduloId', 'cargoId');
    }
    
    /**
     * Métodos desconhecidos serão chamados no modelo usuário
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments) {
        $method = new \ReflectionMethod($this->getWrapped(), $name);
        return $method->invokeArgs($this->getWrapped(), $arguments);
    }

}
