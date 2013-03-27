<?php
namespace login;

use \core\SGAContext;
use \core\view\PageView;

/**
 * LoginView
 *
 * @author rogeriolino
 */
class LoginView extends PageView {
    
    public function header(SGAContext $context) {
        $context->setParameter('bodyClass', 'login');
        return parent::header($context);
    }

    protected function basePath(SGAContext $context) {
        return dirname(__FILE__);
    }
    
}
