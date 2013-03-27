<?php
namespace core\view;

use \core\SGA;
use \core\view\SGAView;
use \core\SGAContext;

/**
 * PageView
 * 
 * O conteudo renderizado e incluido a partir do arquivo especificado via URL (page)
 *
 * @author rogeriolino
 */
abstract class PageView extends SGAView {
    
    /**
     * Caminho base que sera usado para importar os arquivos do modulo
     */
    protected abstract function basePath(SGAContext $context);
    
    /**
     * @param SGAContext $context
     * @return string
     */
    public function content(SGAContext $context) {
        // assigning vars
        $builder = $this->builder;
        foreach ($this->variables as $varname => $varvalue) {
            ${$varname} = $varvalue;
        }
        $page = $context->getParameter(SGA::K_PAGE);
        $filename = $this->basePath($context) . DS . VIEW_DIR . DS . "$page.php";
        if (!file_exists($filename)) {
            throw new \Exception(sprintf(_('Página não encontrada: %s'), $filename));
        }
        // including page
        ob_start();
        require_once($filename);
        $content = ob_get_clean();
        return $content;
    }

}
