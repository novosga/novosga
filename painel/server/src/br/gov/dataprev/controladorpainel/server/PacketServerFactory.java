package br.gov.dataprev.controladorpainel.server;

import br.gov.dataprev.controladorpainel.ConfigManager;

/**
 * Novo SGA - Packet Server Factory
 * 
 * @author rogeriolino
 */
public class PacketServerFactory {
    
    public static PacketServer create(ConfigManager config) {
        int port = config.getNetworkPort();
        if (config.getNetworkProtocol().equalsIgnoreCase("TCP")) {
            return new TCPServer(port);
        }
        return new UDPSimpleListener(port);
    }
    
}
