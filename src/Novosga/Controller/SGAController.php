<?php
namespace Novosga\Controller;

use Novosga\SGA;
use Novosga\Context;

/**
 * SGA module controller
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
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


    public function index(Context $context) {}
    
}
