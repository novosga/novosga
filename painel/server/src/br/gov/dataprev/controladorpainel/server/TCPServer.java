package br.gov.dataprev.controladorpainel.server;

import br.gov.dataprev.controladorpainel.Painel;
import java.io.IOException;
import java.net.InetSocketAddress;
import java.util.logging.Level;
import java.util.logging.Logger;
import br.gov.dataprev.controladorpainel.enviados.ServerMsg;
import java.io.InputStream;
import java.net.ServerSocket;
import java.net.Socket;
import java.nio.ByteBuffer;

/**
 * @author ulysses
 *
 */
public class TCPServer extends PacketServer implements Runnable {

    private static final Logger LOG = Logger.getLogger(TCPServer.class.getName());
    
    private static final int MIN_THREADS = 8;
    private static final int BUFFER_SIZE = 8192;
    private final Thread _thread;
    private ServerSocket _socket;
    private ByteBuffer _bufferLeitura;
    private ByteBuffer _bufferEscrita;

    public TCPServer(int port) {
        super(port, MIN_THREADS);      
        _thread = new Thread(this, "TCP Server Thread");
        _thread.setPriority(Thread.MAX_PRIORITY);
        _bufferLeitura = ByteBuffer.wrap(new byte[BUFFER_SIZE]);
        _bufferEscrita = ByteBuffer.wrap(new byte[BUFFER_SIZE]);
    }

    @Override
    public void start() {
        this.setStatus(ServerStatus.STARTING);
        LOG.info("Starting TCPServer");
        _thread.start();
    }

    @Override
    public void run() {
        try {
            _socket = new ServerSocket(_port);
        } catch (Exception e) {
            LOG.log(Level.SEVERE, "Falha inicializando socket: " + e.getMessage(), e);
            System.exit(2);
        }
        this.setStatus(ServerStatus.RUNNING);
        InetSocketAddress origem = null;
        while (true) {
            try {
                _bufferLeitura.clear();
                Socket client = _socket.accept();
                if (client != null) {
                    int read;
                    int totalRead = 0;
                    InputStream clientInputStream = client.getInputStream();
                    while ((read = clientInputStream.read(_bufferLeitura.array())) != -1) {
                        totalRead += read;
                    }
                    String host = client.getLocalAddress().getHostAddress();
                    origem = new InetSocketAddress(host, client.getPort());
                    this.getPacketHandler().processaDados(origem, _bufferLeitura);
                    client.close();
                }
            } catch (IOException e) {
                LOG.log(Level.SEVERE, "Erro tentando receber pacote TCP", e);
            }
        }
    }

    @Override
    public void envia(ServerMsg msg) {
        // envia no current thread
        synchronized (this) {
            int attempts = 0;
            int maxAttempts = 5;
            while (attempts < maxAttempts) {
                try {
                    _bufferEscrita.clear();
                    String host = msg.getHostRemotoStr();
                    Socket socket = new Socket();
                    socket.connect(new InetSocketAddress(host, Painel.PORT));
                    if (msg.writeTo(_bufferEscrita)) {
                        _bufferEscrita.flip();
                        socket.getOutputStream().write(_bufferEscrita.array());
                    }
                    socket.close();
                    break;
                } catch (IOException e) {
                    // erro ao tentar enviar, incrementa tentativas
                    attempts++;
                    LOG.log(Level.SEVERE, "Falha enviando pacote. Motivo: " + e.getMessage(), e);
                }
            }
        }
    }
}
