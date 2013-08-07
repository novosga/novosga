package org.novosga.painel.client.network;

import java.io.IOException;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.net.InetAddress;
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
    private static ServerSocket _server;

    public TCPListener(int receivePort, int sendPort, String server) {
        super(receivePort, sendPort, server);
    }
    
    @Override
    public void doStart() throws Exception {
        // inicia um servidor local para receber mensagens do controlador de paineis
        _server = new ServerSocket(receivePort);
    }
    
    @Override
    protected void preObterUrls() {
    }

    @Override
    protected void postObterUrls() {
    }

    @Override
    protected void preCadastrarPainel() {
    }

    @Override
    protected void postCadastrarPainel() {
    }

    @Override
    protected void postLeMsgConfirmaCadastro() {
    }

    /**
     * Thread servidor TCP
     */
    @Override
    public void run() {
        ByteBuffer buffer = ByteBuffer.wrap(new byte[BUFFER_SIZE]);
        ExecutorService executor = Executors.newFixedThreadPool(1, new SimpleThreadFactory("TCPListenerThread"));
        try {
            InetAddress serverAddress = InetAddress.getByName(server);
            while (true) {
                try {
                    Socket client = _server.accept();
                    if (client != null) {
                        int read;
                        int totalRead = 0;
                        buffer.clear();
                        if (client.getInetAddress().equals(serverAddress)) {
                            while ((read = client.getInputStream().read(buffer.array())) != -1) {
                                totalRead += read;
                            }
                            buffer.limit(totalRead);
                            LOG.log(Level.FINE, "Pacote recebido (Tamanho: {0})", totalRead);
                            this.lePacote(executor, buffer);
                        } else {
                            LOG.log(Level.WARNING, "Descartando pacote recebido de origem desconhecida: {0}", client.getInetAddress().getHostAddress());
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
                Socket socket = new Socket();
                socket.setTcpNoDelay(true);
                socket.connect(new InetSocketAddress(server, sendPort));
                socket.getOutputStream().write(buffer.array());
                socket.getOutputStream().flush();
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