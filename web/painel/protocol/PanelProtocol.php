<?php
namespace painel\protocol;

/**
 * PanelProtocol
 * @author rogeriolino
 */
interface PanelProtocol {
    
    public function version();
    
    public function encodeUnidades($unidades);
    
    public function encodeServicos($servicos);
    
}
