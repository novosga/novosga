<?php
namespace novosga\view;

/**
 * SGAView
 * 
 * Twig wrapper para manter compatibilidade em caso 
 * de atualizações
 *
 * @author rogeriolino
 */
class SGAView extends \Slim\Views\Twig {
    
    /**
     * Set view data value with key
     * 
     * [ALIAS OF set]
     * 
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value) {
        $this->set($key, $value);
    }
    
}
