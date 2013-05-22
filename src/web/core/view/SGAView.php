<?php
namespace core\view;

use \Exception;
use \core\SGA;
use \core\view\View;
use \core\SGAContext;
use \core\util\Arrays;

/**
 * SGAView
 *
 * @author rogeriolino
 */
abstract class SGAView implements View {

    // TODO: tema dinamico
    const THEME = 'bootstrap';
    
    protected $title;
    /**
     * @var TemplateBuilder
     */
    protected $builder;
    protected $variables = array();


    private static $dependencies = array(
        'js' => array(
            'js/html5.js', 
            'js/jquery.js', 
            'js/jquery-ui.js',
            'js/script.js'
        ),
        'css' => array(
            'css/style.css'
        )
    );
    
    public function __construct($title) {
        $this->title = $title;
        $this->builder = new TemplateBuilder();
    }
    
    /**
     * Adiciona uma variavel ao escopo da pagina
     * @param string $name
     * @param mixed $value
     */
    public function assign($name, $value) {
        $this->variables[$name] = $value;
    }
    
    public final function viewId() {
        return md5(Arrays::value(SGA::K_MODULE, 'sga') . '_' .Arrays::value(SGA::K_PAGE, 'index'));
    }
    
    public function addMessage($message, $class) {
        $context = SGA::getContext();
        $messages = $this->getMessages();
        if (empty($messages)) {
            $messages = array();
        }
        $messages[] = array('text' => $message, 'class' => $class);
        $context->getSession()->set($this->viewId(), $messages);
    }
    
    public function getMessages() {
        $context = SGA::getContext();
        return $context->getSession()->get($this->viewId());
    }
    
    public function showMessages() {
        $messages = $this->getMessages();
        if (!empty($messages)) {
            $html = '<div class="messages">';
            foreach ($messages as $message) {
                if ($message['class'] == 'success') {
                    $html .= $this->builder->success($message['text']);
                }
                else if ($message['class'] == 'error') {
                    $html .= $this->builder->error($message['text']);
                }
            }
            return $html . '</div>';
        }
        return '';
    }

    /**
     * @return TemplateBuilder
     */
    public function getBuilder() {
        return $this->builder;
    }
    
    /**
     * Returns the page content
     * @param SGAContext $context
     * @return string
     */
    public abstract function content(SGAContext $context);
    
    /**
     * Returns SGA header html
     * @param SGAContext $context
     * @return string
     */
    public function header(SGAContext $context) {
        $arg = $context->getParameters();
        $bodyClass = Arrays::value($arg, 'bodyClass');
        $title = $this->title;
        if (!empty($title)) {
            $title .= ' | ';
        }
        $title .= SGA::NAME;
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title><?php SGA::out($title) ?></title>
    <?php $this->headerDependencies($context); ?>
</head>
<body class="<?php SGA::out($bodyClass) ?>">
    <div id="geral">
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    protected function headerDependencies(SGAContext $context) {
        $arg = $context->getParameters();
        $scripts = Arrays::value($arg, 'js', array());
        $scripts = array_merge(self::$dependencies['js'], $scripts);
        $styles = Arrays::value($arg, 'css', array());
        $styles = array_merge(self::$dependencies['css'], $styles);
        $styles[] = "themes/" . self::THEME . "/style.css";
        foreach ($styles as $style): ?>
        <link type="text/css" rel="stylesheet" href="<?php SGA::out($style . '?v=' . SGA::VERSION) ?>" />
        <?php endforeach; ?>
        <!--[if lt IE 9]>
        <script src="js/html5.js"></script>
        <![endif]-->
        <?php foreach ($scripts as $script): ?>
        <script type="text/javascript" src="<?php SGA::out($script . '?v=' . SGA::VERSION) ?>"></script>
        <?php endforeach; ?>
        <script type="text/javascript">
            SGA.K_MODULE = '<?php echo SGA::K_MODULE ?>'; 
            SGA.K_PAGE = '<?php echo SGA::K_PAGE ?>'; 
            SGA.version = '<?php echo SGA::VERSION ?>';
            SGA.module = '<?php SGA::out(defined('MODULE') ? MODULE : '') ?>';
            SGA.invalidSession = '<?php SGA::out(_(\login\LoginController::INVALID_SESSION)); ?>';
            SGA.inactiveSession = '<?php SGA::out(_(\login\LoginController::INACTIVE_SESSION)); ?>';
            SGA.dialogs.error.title = '<?php SGA::out(_('Erro')) ?>';
            SGA.dateFormat = '<?php echo _('d/m/Y') ?>';
        </script>
        <link rel="shortcut icon" href="images/favicon.png" />
        <?php
    }
    
    /**
     * Returns SGA footer html
     * @param SGAContext $context
     * @return string
     */
    public function footer(SGAContext $context) {
        return '</div></body></html>';
    }
    
    /**
     * Render the SGA page
     * @param SGAContext $context
     * @return string
     */
    public function render(SGAContext $context) {
        try {
            $this->assign('view', $this);
            $content = $this->content($context);
            if (!$context->getResponse()->renderView()) {
                return $content;
            }
            $html = $this->header($context);
            $html .= $content;
            $html .= $this->footer($context);
            // limpando as mensagens da view
            $context->getSession()->del($this->viewId());
            return $html;
        } catch (Exception $e) {
            $view = new ErrorView();
            $context->setParameter('exception', $e);
            return $view->render($context);
        }
    }

}
