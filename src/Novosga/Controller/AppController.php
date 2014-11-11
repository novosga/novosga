<?php
namespace Novosga\Controller;

use Novosga\App;
use Novosga\Context;

/**
 * App module controller
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AppController {
    
    private $app;
    
    public function __construct(App $app) {
        $this->app = $app;
    }
    
    /**
     * @return App
     */
    public final function app() {
        return $this->app;
    }


    public function index(Context $context) {}
    
}
