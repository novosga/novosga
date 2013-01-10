<?php
namespace install;

require_once('InstallStep.php');
require_once('InstallData.php');

use \core\SGA;
use \core\SGAContext;
use \core\util\Arrays;
use \core\view\SGAView;

/**
 * 
 */
class InstallView extends SGAView {
    
    const ERROR = 'SGA_INSTALL_ERROR';
    
    public function __construct() {
        parent::__construct(_('Instalação'));
    }
    
    public function header(SGAContext $context) {
        $arg = $context->getParameters();
        // appending installl js script
        $arg['js'] = Arrays::value($arg, 'js', array());
        $arg['js'][] = 'install/js/script.js';
        // appending install css style
        $arg['css'] = Arrays::value($arg, 'css', array());
        $arg['css'][] = 'install/css/style.css';
        $context->setParameters($arg);
        return parent::header($context);
    }
    
    public function progress($steps, $currStep) {
        $html = '<ul id="progress">';
        foreach ($steps as $step) {
            $class = ($step->getId() == $currStep->getId()) ? ' class="current"' : '';
            $html .= '<li'. $class .'><span title="' . $step->getTitle() . '">' . ($step->getId() + 1) . '</span></li>';
        }
        $html .= '</ul>';
        return $html;
    }
    
    public function content(SGAContext $context) {
        $steps = $context->getParameter(InstallController::STEPS);
        $totalSteps = $context->getParameter(InstallController::TOTAL_STEPS);
        $index = $context->getParameter(InstallController::CURR_STEP_IDX);
        $currStep = $context->getParameter(InstallController::CURR_STEP);
        $html = $this->progress($steps, $currStep);
        ob_start();
        ?>
        <div id="install_panel">
            <script type="text/javascript">
                SGA.Install.stepKey = '<?php SGA::out(SGA::K_INSTALL) ?>';
                SGA.Install.pageKey = '<?php SGA::out(SGA::K_PAGE) ?>';
                SGA.Install.currStep = <?php SGA::out($index) ?>; 
                SGA.Install.totalSteps = <?php SGA::out($totalSteps) ?>;
            </script>
            <div class="step">
                <?php
                    $context->getSession()->set(InstallView::ERROR, false);
                    echo $this->doStep($steps, $index);
                ?>
            </div>
        </div>
        <div id="install_title" class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all">
            <div class="navegation">
                <?php
                    if ($index > 0) {
                        $prev = $steps[$index - 1];
                        // exibindo o botao voltar
                        echo $this->builder->button(array(
                            'id' => 'btn_prev',
                            'label' => 'Anterior',
                            'onclick' => 'SGA.Install.prevStep()',
                            'title' => $prev->getTitle()
                        ));
                    }
                    if ($index < $totalSteps - 1) {
                        $next = $steps[$index + 1];
                        // exibindo o botao avancar
                        $btnArgs = array(
                            'id' => 'btn_next',
                            'label' => 'Próximo',
                            'onclick' => "SGA.Install.nextStep()",
                            'class' => 'ui-button-primary',
                            'title' => $next->getTitle()
                        );
                        if ($context->getSession()->get(InstallView::ERROR)) {
                            $btnArgs['disabled'] = "disabled";
                        }
                        echo $this->builder->button($btnArgs);
                    }
                ?>
            </div>
            <h1>Instalação: <?php SGA::out($currStep->getTitle()) ?></h1>
        </div>
        <?php
        $html .= ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function doStep($steps, $index) {
        if (isset($steps[$index])) {
            return SGA::import(basename(dirname(__FILE__)) . DS . VIEW_DIR . DS . 'step' . $index . '.php', true);
        } else {
            return 'Unkown install step: ' . $step;
        }
    }

}
