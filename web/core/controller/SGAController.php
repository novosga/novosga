<?php
namespace core\controller;

use \core\SGAContext;

/**
 * SGA module controller
 *
 * @author rogeriolino
 */
abstract class SGAController {
    
    private $view;
        
    /**
     * @return SGAView
     */
    public function view() {
        if (!$this->view) {
            $this->view = $this->createView();
        }
        return $this->view;
    }
    
    /**
     * @return SGAView
     */
    protected abstract function createView();
    
    public function index(SGAContext $context) {}
    
}
