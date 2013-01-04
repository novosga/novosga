<?php
namespace painel\protocol;

/**
 * Protocolo original do SGA Livre
 *
 * @author rogeriolino
 */
class SGALivreProtocol implements PanelProtocol {
    
    public function version() {
        return 1;
    }
    
    public function encodeServicos($servicos) {
        $response = '';
        foreach ($servicos as $s) {
            $response .= "{$s->getServico()->getId()}#{$s->getSigla()}#{$s->getNome()}\n";
        }
        return $response;
    }

    public function encodeUnidades($unidades) {
        $response = '';
        foreach ($unidades as $u) {
            $response .= "{$u->getId()}#{$u->getCodigo()}#{$u->getNome()}\n";
        }
        return $response;
    }
    
}
