package org.novosga.painel.client.network;

import java.io.IOException;
import java.net.DatagramPacket;
import java.net.DatagramSocket;
import java.net.InetAddress;
import java.net.InetSocketAddress;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.TimeUnit;

public class UDPListener extends PacketListener {

    private static final Logger LOG = Logger.getLogger(UDPListener.class.getName());
    private static final int UDP_TIMEOUT = 10;
    private DatagramSocket _socket;
    // Semaphoros
    private CountDownLatch _latchCadastro;
    private CountDownLatch _latchObterURLs;

    public UDPListener(int receivePort, int sendPort, String server) {
        super(receivePort, sendPort, server);
    }
    
    @Override
    public void doStart() throws Exception {
        _socket = new DatagramSocket(receivePort);
    }

    /**
     * Implementação do Thread servidor UDP
     */
    @Override
    public void run() {
        if (_socket != null) {
            byte[] buffer = new byte[4096];
            DatagramPacket dp = new DatagramPacket(buffer, 4096);
            ByteBuffer buf = ByteBuffer.wrap(buffer);
            ExecutorService executor = Executors.newFixedThreadPool(1, new SimpleThreadFactory("UDPListenerThread"));
            try {
                while (true) {
                    try {
                        buf.clear();
                        _socket.receive(dp);
                        InetSocketAddress sa = (InetSocketAddress) dp.getSocketAddress();
                        LOG.log(Level.FINE, "Pacote recebido (Tamanho: {0} Origem: {1})", new Object[]{dp.getLength(), dp.getSocketAddress()});
                        InetAddress serverAddress = InetAddress.getByName(server);
                        // só aceitar pacotes originados do servidor
                        if (sa.getAddress().equals(serverAddress)) {
                            try {
                                this.lePacote(executor, buf);
                            } catch (Throwable t) {
                                LOG.log(Level.SEVERE, t.getMessage(), t);
                            }
                        } else {
                            LOG.log(Level.WARNING, "Descartando pacote recebido de origem desconhecida: {0}", sa.getAddress().toString());
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
        DatagramPacket dp = new DatagramPacket(buffer.array(), 0, buffer.position(), new InetSocketAddress(server, sendPort));
        _socket.send(dp);
    }
    
    @Override
    protected void postLeMsgConfirmaCadastro() {
        // sinaliza recebimento de confirmação de cadastro
        _latchCadastro.countDown();
    }
    
    @Override
    protected void postLeMsgUrls() {
        _latchObterURLs.countDown();
    }
    
    @Override
    protected void preObterUrls() {
        _latchObterURLs = new CountDownLatch(1);
    }
    
    @Override
    protected void postObterUrls() {
        boolean ok = false;
        try {
            ok = _latchObterURLs.await(UDP_TIMEOUT, TimeUnit.SECONDS);
        } catch (InterruptedException e) {
            // nada
        }
        // timeout?
        if (!ok) {
            throw new RuntimeException("Tempo de espera pela resposta (" + UDP_TIMEOUT + " segundos) esgotado.");
        }
    }
    
    @Override
    protected void preCadastrarPainel() {
        _latchCadastro = new CountDownLatch(1);
    }
    
    @Override
    protected void postCadastrarPainel() {
        boolean ok = false;
        try {
            ok = _latchCadastro.await(UDP_TIMEOUT, TimeUnit.SECONDS);
            System.err.println("LATCH CADASTRO OK");
        } catch (InterruptedException e) {
        }
        // timeout?
        if (!ok) {
            throw new RuntimeException("Tempo de espera pela resposta (" + UDP_TIMEOUT + " segundos) esgotado.");
        }
    }

}