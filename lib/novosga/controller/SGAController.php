<?php
namespace novosga\controller;

use \novosga\SGA;
use \novosga\SGAContext;

/**
 * SGA module controller
 *
 * @author rogeriolino
 */
abstract class SGAController {
    
    private $app;
    
    public function __construct(SGA $app) {
        $this->app = $app;
    }
    
    /**
     * @return SGA
     */
    public final function app() {
        return $this->app;
    }


    public function index(SGAContext $context) {}
    
}
