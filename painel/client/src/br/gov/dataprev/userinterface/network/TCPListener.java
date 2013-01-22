package br.gov.dataprev.userinterface.network;

import java.io.IOException;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.logging.Level;
import java.util.logging.Logger;
import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import java.net.InetSocketAddress;
import java.net.ServerSocket;
import java.net.Socket;

/**
 * 
 * @author rogeriolino
 */
public class TCPListener extends PacketListener {

    private static final Logger LOG = Logger.getLogger(TCPListener.class.getName());
    private static final int BUFFER_SIZE = 8192;
    private ServerSocket _server;

    public TCPListener(int port) {
        super(port);
    }
    
    @Override
    public void doStart() throws Exception {
        // inicia um servidor local para receber mensagens do controlador de paineis
        _server = new ServerSocket(_port);
    }

    /**
     * Thread servidor TCP
     */
    @Override
    public void run() {
        ByteBuffer buffer = ByteBuffer.wrap(new byte[BUFFER_SIZE]);
        ExecutorService executor = Executors.newFixedThreadPool(1);
        try {
            while (true) {
                try {
                    Socket client = _server.accept();
                    if (client != null) {
                        int read;
                        int totalRead = 0;
                        buffer.clear();
                        while ((read = client.getInputStream().read(buffer.array())) != -1) {
                            totalRead += read;
                        }
                        LOG.fine("Pacote recebido (Tamanho: " + totalRead + ")");
                        try {
                            this.lePacote(executor, buffer);
                        } catch (Throwable t) {
                            LOG.log(Level.SEVERE, "Pacote recebido (Tamanho: " + totalRead + ")", t);
                        }
                        client.close();
                    }
                } catch (IOException e) {
                    LOG.log(Level.SEVERE, "Erro recebendo pacote", e);
                }
            }
        } catch (Exception e) {
            executor.shutdownNow();
            LOG.fine("Processador de senhas encerrado");
        }
    }
    
    @Override
    protected void send(ByteBuffer buffer) throws Exception {
        int attempts = 0;
        int maxAttempts = 5;
        while (attempts < maxAttempts) {
            try {
                String serverName = ConfiguracaoGlobal.getInstance().getIPServer();
                int serverPort = ConfiguracaoGlobal.getInstance().getPort();
                Socket socket = new Socket();
                socket.connect(new InetSocketAddress(serverName, serverPort));
                socket.getOutputStream().write(buffer.array());
                socket.close();
                // sai do loop em caso de sucesso
                break; 
            } catch (IOException e) {
                // erro ao tentar enviar, incrementa tentativas
                attempts++;
                LOG.log(Level.SEVERE, "Falha enviando pacote. Motivo: " + e.getMessage(), e);
            }
        }
        if (attempts >= maxAttempts) {
            String message = "Erro ao tentar enviar pacote TCP. Número máximo de tentativas esgotados";
            LOG.log(Level.SEVERE, message);
            throw new Exception(message);
        }
    }

}