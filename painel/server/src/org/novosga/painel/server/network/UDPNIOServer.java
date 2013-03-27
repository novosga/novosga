package org.novosga.painel.server.network;

import java.io.IOException;
import java.net.DatagramSocket;
import java.net.InetSocketAddress;
import java.net.SocketAddress;
import java.net.SocketException;
import java.nio.ByteBuffer;
import java.nio.channels.ClosedChannelException;
import java.nio.channels.DatagramChannel;
import java.nio.channels.SelectionKey;
import java.nio.channels.Selector;
import java.util.Iterator;
import java.util.LinkedList;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.novosga.painel.server.network.send.ServerMsg;

/**
 * Classe responsavel pelo servidor UDP de alta escalabilidade.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class UDPNIOServer extends UDPServer implements Runnable {

    private static final Logger LOG = Logger.getLogger(UDPNIOServer.class.getName());
    
    private final ByteBuffer _bufferLeitura;
    private final ByteBuffer _bufferEscrita;
    private volatile LinkedList<ServerMsg> _listaEnvioExterna = new LinkedList<ServerMsg>();
    private LinkedList<ServerMsg> _listaEnvioInterna = new LinkedList<ServerMsg>();
    private final Thread _thread;
    private SelectionKey _sk;

    public UDPNIOServer(int port) {
        super(port);
        _thread = new Thread(this, "UDP Server Thread - NIO Selector");
        _thread.setPriority(Thread.MAX_PRIORITY);
        _bufferLeitura = ByteBuffer.allocateDirect(8192);
        _bufferEscrita = ByteBuffer.allocateDirect(8192);
    }

    @Override
    public void start() {
        this.setStatus(ServerStatus.STARTING);
        LOG.info("Starting UDPNIOServer");
        _thread.start();
    }

    @Override
    public void run() {
        DatagramSocket ds = null;
        try {
            ds = DatagramChannel.open().socket();
            ds.getChannel().configureBlocking(false);
            ds.bind(new InetSocketAddress(9999));
        } catch (SocketException e) {
            LOG.severe("Falha abrindo socket UDP. Motivo: " + e.getLocalizedMessage());
            System.exit(1);
        } catch (IOException e) {
            LOG.severe("Falha colocando socket UDP em modo não bloqueante. Motivo: " + e.getLocalizedMessage());
            System.exit(2);
        }
        Selector selector = null;
        try {
            selector = Selector.open();
        } catch (IOException e) {
            LOG.severe("Falha abrindo Seletor de sockets. Motivo: " + e.getLocalizedMessage());
            System.exit(3);
        }
        try {
            _sk = ds.getChannel().register(selector, SelectionKey.OP_READ);
        } catch (ClosedChannelException e) {
            LOG.severe("Falha registrando Socket no Seletor de sockets. Motivo: " + e.getLocalizedMessage());
            System.exit(5);
        }
        int totalKeys = 0;
        Iterator<SelectionKey> iterator;
        SelectionKey sk;
        this.setStatus(ServerStatus.RUNNING);
        try {
            // LOOP INFINITO
            for (;;) {
                try {
                    totalKeys = selector.selectNow();
                } catch (IOException e) {
                    LOG.severe("Falha efetuando select de sockets. Motivo: " + e.getLocalizedMessage());
                    totalKeys = 0;
                }
                if (totalKeys > 0) {
                    iterator = selector.selectedKeys().iterator();
                    LOG.fine("Selected Keys: " + totalKeys);
                    while (iterator.hasNext()) {
                        sk = iterator.next();
                        switch (sk.readyOps()) {
                            case SelectionKey.OP_READ:
                                this.read(sk);
                                break;
                            case SelectionKey.OP_READ | SelectionKey.OP_WRITE:
                                this.read(sk);
                                if (sk.isValid()) {
                                    this.write(sk);
                                }
                                break;
                            case SelectionKey.OP_WRITE:
                                this.write(sk);
                                break;
                            default:
                                LOG.severe("Unexpected SelectionKey readyOps:: " + sk.readyOps());
                        }
                    }
                    iterator.remove();
                }
                this.write(_sk);
                // ceder CPU para outros threads
                try {
                    Thread.sleep(1);
                } catch (InterruptedException e) {
                    // nada
                }
            }
        } catch (Throwable t) {
            LOG.log(Level.SEVERE, "UDP Thread crash. Reason: " + t.getMessage(), t);
        } finally {
            LOG.info("UDP Thread stopped");
        }
    }

    protected Iterator<ServerMsg> processaListas() {
        synchronized (_listaEnvioExterna) {
            _listaEnvioInterna.addAll(_listaEnvioExterna);
            _listaEnvioExterna.clear();
            return _listaEnvioInterna.iterator();
        }
    }

    @Override
    public void envia(ServerMsg msg) {
        synchronized (_listaEnvioExterna) {
            _listaEnvioExterna.add(msg);
        }
    }

    /**
     * @param sk
     */
    private void write(SelectionKey sk) {
        Iterator<ServerMsg> iterator = this.processaListas();
        ServerMsg msg;
        while (iterator.hasNext()) {
            msg = iterator.next();
            if (this.sendMsg(sk, msg)) {
                // se enviado com sucesso, remove da lista
                iterator.remove();
            }
        }
    }

    /**
     * @param msg
     */
    private boolean sendMsg(final SelectionKey sk, final ServerMsg msg) {
        _bufferEscrita.clear();
        DatagramChannel channel = (DatagramChannel) sk.channel();
        if (msg.writeTo(_bufferEscrita)) {
            _bufferEscrita.flip();
            int remaining = _bufferEscrita.remaining();
            int sent = 0;
            try {
                sent = channel.send(_bufferEscrita, msg.getSocketAddress());
            } catch (IOException e) {
                LOG.log(Level.SEVERE, "Falha enviando datagrama UDP. Motivo: " + e.getLocalizedMessage() + " - Pacote: " + msg, e);
                return false;
            }

            if (sent == 0) {
                // nada foi enviado, retorna false (mantem pacote na fila pra re-envio futuro)
                LOG.severe("Pacote não enviado, provavelmente o buffer de saída do socket está cheio. Pacote: " + msg);
                return false;
            } else if (sent == remaining) {
                // conteudo foi totalmente enviado, retorna true (remove o pacote da fila de envio)
                return true;
            } else {
                // Segundo a API do socket, ou todo conteudo é enviado ou nada.
                // Logo essa situação nunca deverá ocorrer.
                LOG.severe("Erro de envio inesperado, (sent = " + sent + ", remaining=" + remaining + "). Pacote: " + msg);
                return false;
            }
        }
        return true;
    }

    private void read(SelectionKey sk) {
        DatagramChannel channel = (DatagramChannel) sk.channel();
        SocketAddress origem;
        int rc = 0;
        do {
            try {
                _bufferLeitura.clear();
                origem = channel.receive(_bufferLeitura);
                if (origem != null) {
                    rc++;
                    if (origem instanceof InetSocketAddress) {
                        _bufferLeitura.flip();
                        this.getPacketHandler().processaDados((InetSocketAddress) origem, _bufferLeitura);
                    } else {
                        // nunca deve acontecer já que o socket efetua bind pelo protocolo IP
                        // exceto numa possível modificação de codigo
                        LOG.warning("Recebido pacote de Protocolo desconhecido/não suportado(não IP): " + origem.getClass().getSimpleName());
                    }
                }
            } catch (IOException e) {
                LOG.warning("Erro recebendo Datagrama UDP.");
                origem = null;
            }
        } while (origem != null);
        LOG.info("Efetuei " + rc + " leituras");
    }
}
