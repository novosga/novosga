<?php
namespace modules\sga\admin;

use \core\controller\ModuleController;

/**
 * AdminView
 * @author rogeriolino
 */
class AdminController extends ModuleController {

    public function __construct() {
        $this->title = _('Administração');
        $this->subtitle = _('Configurações gerais do sistema');
    }
  
}
