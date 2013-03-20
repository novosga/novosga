<?php
namespace core\view;

use \core\SGAContext;

/**
 * View interface
 *
 * @author rogeriolino
 */
interface View {

    /**
     * 
     * @param SGAContext $context
     */
    public function render(SGAContext $context);
    
}
