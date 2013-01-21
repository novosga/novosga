package br.gov.dataprev.controladorpainel.server;

import br.gov.dataprev.controladorpainel.server.PacketServer;

/**
 * @author ulysses
 *
 */
public abstract class UDPServer extends PacketServer {

    private static final int MIN_THREADS = 8;

    public UDPServer(int port) {
        super(port, MIN_THREADS);
    }
    
}
