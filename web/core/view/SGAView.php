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
        $scripts = Arrays::value($arg, 'js', array());
        $scripts = array_merge(self::$dependencies['js'], $scripts);
        $styles = Arrays::value($arg, 'css', array());
        $styles = array_merge(self::$dependencies['css'], $styles);
        // TODO: tema dinamico
        $theme = 'bootstrap';
//        $theme = 'lightness';
        $styles[] = "themes/$theme/style.css";
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title><?php echo $title; ?></title>
    <?php foreach ($styles as $style): ?>
    <link type="text/css" rel="stylesheet" href="<?php echo $style . '?v=' . SGA::VERSION ?>" />
    <?php endforeach; ?>
    <!--[if lt IE 9]>
    <script src="js/html5.js"></script>
    <![endif]-->
    <?php foreach ($scripts as $script): ?>
    <script type="text/javascript" src="<?php echo $script . '?v=' . SGA::VERSION ?>"></script>
    <?php endforeach; ?>
    <script type="text/javascript">
        SGA.K_MODULE = '<?php echo SGA::K_MODULE ?>'; SGA.K_PAGE = '<?php echo SGA::K_PAGE ?>'; SGA.module = '<?php echo defined('MODULE') ? MODULE : '' ?>';
    </script>
    <link rel="shortcut icon" href="images/favicon.png" />
</head>
<body class="<?php echo $bodyClass ?>">
    <div id="geral">
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
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
            $content = $this->content($context);
            if (!$context->getResponse()->renderView()) {
                return $content;
            }
            $html = $this->header($context);
            $html .= $content;
            $html .= $this->footer($context);
            return $html;
        } catch (Exception $e) {
            $view = new ErrorView();
            $context->setParameter('exception', $e);
            return $view->render($context);
        }
    }

}
