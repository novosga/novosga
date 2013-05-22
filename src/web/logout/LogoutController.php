<?php
namespace logout;

use \core\SGA;
use \core\SGAContext;
use \core\controller\SGAController;

/**
 * LogoutController
 *
 * @author rogeriolino
 */
class LogoutController extends SGAController {
    
    
    public function index(SGAContext $context) {
        $context->getSession()->destroy();
        header('Location:./');
        exit();
    }

    // nao tem view
    protected function createView() {
        return null;
    }
}

