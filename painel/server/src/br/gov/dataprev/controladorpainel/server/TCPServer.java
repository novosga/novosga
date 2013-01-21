package br.gov.dataprev.controladorpainel.server;

import java.io.IOException;
import java.net.InetSocketAddress;
import java.util.logging.Level;
import java.util.logging.Logger;
import br.gov.dataprev.controladorpainel.enviados.ServerMsg;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.ServerSocket;
import java.net.Socket;
import java.net.SocketAddress;
import java.nio.ByteBuffer;
import java.util.HashMap;

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
    private static final HashMap<String, Socket> _clients = new HashMap<String, Socket>();

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
            _socket.setSoTimeout(0);
            //_socket.getChannel().configureBlocking(false);
        } catch (Exception e) {
            LOG.log(Level.SEVERE, "Falha inicializando socket: " + e.getMessage(), e);
            System.exit(2);
        }
        final TCPServer server = this;
        this.setStatus(ServerStatus.RUNNING);
        Runnable t = new Runnable() {

            @Override
            public void run() {
                server.liten();
            }
            
        };
        t.run();
    }
    
    public void liten() {
        InetSocketAddress origem = null;
        while (true) {
            try {
                _bufferLeitura.clear();
                Socket client = _socket.accept();
                if (client != null) {
                    client.getInputStream();
                    int read;
                    int totalRead = 0;
                    InputStream clientInputStream = client.getInputStream();
                    while ((read = clientInputStream.read(_bufferLeitura.array())) != -1) {
                        totalRead += read;
                    }
                    String host = client.getLocalAddress().getHostAddress();
                    origem = new InetSocketAddress(host, client.getPort());
                    this.getPacketHandler().processaDados(origem, _bufferLeitura);
                    _clients.put(host, client);
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
            _bufferEscrita.clear();
            String host = msg.getHostRemotoStr();
            if (_clients.containsKey(host)) {
                Socket client = _clients.get(host);
                if (msg.writeTo(_bufferEscrita)) {
                    _bufferEscrita.flip();
                    try {
                        OutputStream clientOutputStream = client.getOutputStream();
                        clientOutputStream.write(_bufferEscrita.array());
                    } catch (IOException e) {
                        LOG.log(Level.SEVERE, "Falha enviando pacote. Motivo: " + e.getMessage(), e);
                    }
                }
            }
        }
    }
}
