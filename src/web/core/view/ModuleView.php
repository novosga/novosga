<?php
namespace core\view;

use \core\view\LoggedView;
use \core\SGAContext;
use \core\util\Arrays;

/**
 * ModuleView
 *
 * @author rogeriolino
 */
class ModuleView extends LoggedView {
    
    protected $subtitle;
    
    public function __construct($title, $subtitle = '') {
        parent::__construct($title);
        $this->subtitle = $subtitle;
    }

    protected function basePath(SGAContext $context) {
        return $context->getModulo()->getFullPath();
    }
        
    public function header(SGAContext $context) {
        $arg = $context->getParameters();
        $dir = MODULES_DIR . '/' . str_replace('.', '/', $context->getModulo()->getChave());
        // appending module js script
        $arg['js'] = Arrays::value($arg, 'js', array());
        $arg['js'][] = $dir . '/js/script.js';
        // appending module css style
        $arg['css'] = Arrays::value($arg, 'css', array());
        $arg['css'][] = $dir . '/css/style.css';
        $context->setParameters($arg);
        return parent::header($context);
    }
    
    public function content(SGAContext $context) {
        $content = parent::content($context);
        if ($context->getResponse()->renderView()) {
            $header = '<div class="module-content"><div class="header">';
            $header .= $this->builder->tag('img', array('src' => $context->getModulo()->getPath() . DS . 'icon.png'));
            $header .= $this->builder->tag('h2', $this->title);
            $header .= $this->builder->tag('p', $this->subtitle);
            $header .= '</div>';
            $content = $header . $this->showMessages() . $content;
        }
        return $content;
    }
    
    public function footer(SGAContext $context) {
        return '</div>' . parent::footer($context);
    }

}
