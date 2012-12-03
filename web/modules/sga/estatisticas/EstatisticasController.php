<?php
namespace modules\sga\estatisticas;

use \core\SGAContext;
use \core\controller\ModuleController;

/**
 * EstatisticasController
 *
 * @author rogeriolino
 */
class EstatisticasController extends ModuleController {
    
    public function __construct() {
        $this->title = _('Estatisticas');
        $this->subtitle = _('Visualize e exporte estastísticas e relatórios sobre o sistema');
    }

    public function index(SGAContext $context) {
        $unidade = $context->getUser()->getUnidade();
        $this->view()->assign('unidade', $unidade);
    }
    
}
