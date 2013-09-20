<?php
namespace novosga\controller;

use \novosga\SGA;
use \novosga\db\DB;
use \novosga\model\Modulo;
use \novosga\view\ModuleView;
use \novosga\controller\SGAController;

/**
 * Classe pai dos controladores dos modulos
 *
 * @author rogeriolino
 */
abstract class ModuleController extends SGAController {
    
    protected $title;
    protected $subtitle;
    
    public function __construct(SGA $app, Modulo $modulo) {
        parent::__construct($app);
        $this->title = _($modulo->getNome());
        $this->subtitle = _($modulo->getDescricao());
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em() {
        return DB::getEntityManager();
    }
    
}
