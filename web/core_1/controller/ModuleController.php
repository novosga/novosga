<?php
namespace core\controller;

use \core\db\DB;
use core\SGA;
use \core\view\ModuleView;
use \core\controller\SGAController;

/**
 * Classe pai dos controladores dos modulos
 *
 * @author rogeriolino
 */
abstract class ModuleController extends SGAController {
    
    protected $title;
    protected $subtitle;
    
    public function __construct() {
        $modulo = SGA::getContext()->getModulo();
        $this->title = _($modulo->getNome());
        $this->subtitle = _($modulo->getDescricao());
    }


    /**
     * @return ModuleView
     */
    protected function createView() {
        return new ModuleView($this->title, $this->subtitle);
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function em() {
        return DB::getEntityManager();
    }
    
}
