package br.gov.dataprev.controladorpainel.server;

import br.gov.dataprev.controladorpainel.enviados.ServerMsg;
import java.util.concurrent.CountDownLatch;

/**
 * Novo SGA - Packet Server
 * 
 * @author ulysses
 * @author rogeriolino
 */
public abstract class PacketServer {
    
    private ServerStatus _status = ServerStatus.STOPPED;
    
    protected final int _port;
    protected final PacketHandler _pHandler;
    protected final CountDownLatch _latch = new CountDownLatch(1);
    
    public PacketServer(int port, int numThreads) {
        _port = port;
        _pHandler = new PacketHandler(numThreads);
    }
    
    public abstract void start();

    public abstract void envia(ServerMsg msg);
    
    public void aguardaInicio() {
        try {
            _latch.await();
        } catch (InterruptedException e) {
        }
    }

    protected void setStatus(ServerStatus status) {
        if (status == ServerStatus.RUNNING) {
            _latch.countDown();
        }
        _status = status;
    }

    public ServerStatus getStatus() {
        return _status;
    }

    protected PacketHandler getPacketHandler() {
        return _pHandler;
    }
    
}
