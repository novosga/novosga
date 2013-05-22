<?php
namespace cron;

use \core\SGAContext;
use \core\view\PageView;

/**
 * CronView
 * 
 * @author rogeriolino
 */
class CronView extends PageView {
    
    public function __construct() {
        parent::__construct(_('Cron'));
    }
    
    protected function basePath(SGAContext $context) {
        return dirname(__FILE__);
    }
    
}
