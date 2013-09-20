<?php
namespace painel\protocol;

/**
 * ProtocolFactory
 * @author rogeriolino
 */
class ProtocolFactory {
    
    /**
     * Retorna o interpretador do protocolo a partir da versao informada
     * @param type $version
     * @return \painel\protocol\PanelProtocol
     * @throws \Exception
     */
    public static function create($version) {
        $version = (int) $version;
        if ($version <= 1) {
            return new SGALivreProtocol();
        }
        throw new \Exception(_(sprintf('Nenhum protocolo de painel disponível para a versão especificada: %s', $version)));
    }
    
}
