<?php
namespace home;

use \core\SGAContext;
use \core\view\LoggedView;

/**
 * HomeView
 * 
 * @author rogeriolino
 *
 */
class HomeView extends LoggedView {
    
    public function __construct() {
        parent::__construct(_('Início'));
    }
    
    protected function basePath(SGAContext $context) {
        return dirname(__FILE__);
    }
    
}
