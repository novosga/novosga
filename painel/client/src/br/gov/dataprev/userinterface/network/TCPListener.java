package br.gov.dataprev.userinterface.network;

import java.io.IOException;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.logging.Level;
import java.util.logging.Logger;
import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import java.io.InputStream;
import java.net.Socket;

/**
 * 
 * @author rogeriolino
 */
public class TCPListener extends PacketListener {

    private static final Logger LOG = Logger.getLogger(TCPListener.class.getName());
    private Socket _socket;
    
    @Override
    public void doStart() throws Exception {
        String serverName = ConfiguracaoGlobal.getInstance().getIPServer();
        int serverPort = ConfiguracaoGlobal.getInstance().getPort();
        _socket = new Socket(serverName, serverPort);
    }

    /**
     * Thread servidor TCP
     */
    @Override
    public void run() {
        if (_socket != null) {
            byte[] buffer = new byte[4096];
            ByteBuffer buf = ByteBuffer.wrap(buffer);
            ExecutorService executor = Executors.newFixedThreadPool(1);
            try {
                while (true) {
                    try {
                        buf.clear();
                        int read;
                        int totalRead = 0;
                        InputStream clientInputStream = _socket.getInputStream();
                        while ((read = clientInputStream.read(buf.array())) != -1) {
                            totalRead += read;
                        }
                        LOG.fine("Pacote recebido (Tamanho: " + totalRead);

                        try {
                            this.lePacote(executor, buf);
                        } catch (Throwable t) {
                            LOG.log(Level.SEVERE, "Pacote recebido (Tamanho: " + totalRead, t);
                        }
                    } catch (IOException e) {
                        LOG.log(Level.SEVERE, "Erro recebendo pacote", e);
                    }
                }
            } finally {
                executor.shutdownNow();
                LOG.fine("Processador de senhas encerrado");
            }
        }
    }
    
    @Override
    protected void send(ByteBuffer buffer) throws Exception {
        _socket.getOutputStream().write(buffer.array());
    }

}