/**
 *
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da
 * Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do
 * Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro
 * dos termos da Licença Pública Geral GNU como publicada pela Fundação do
 * Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer
 * versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA
 * GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO ou
 * APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores
 * detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, escreva para a Fundação do
 * Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301
 * USA.
 *
 *
 */
package br.gov.dataprev.userinterface.network;

import java.io.IOException;
import java.net.DatagramPacket;
import java.net.DatagramSocket;
import java.net.InetAddress;
import java.net.InetSocketAddress;
import java.net.SocketException;
import java.nio.ByteBuffer;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.logging.Level;
import java.util.logging.Logger;
import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.TimeUnit;

public class UDPListener extends PacketListener {

    private static final Logger LOG = Logger.getLogger(UDPListener.class.getName());
    private DatagramSocket _socket;
    // Semaphoros
    private CountDownLatch _latchCadastro;
    private CountDownLatch _latchObterURLs;

    public UDPListener(int port) {
        super(port);
    }
    
    /**
     * Inicia o processo de escuta/recebimento na porta UDP.<br>
     *
     * @throws SocketException Se não foi possivél abrir o socket UDP
     * (possivelmente já existe outro painel usando a porta).
     */
    @Override
    public void doStart() throws Exception {
        _socket = new DatagramSocket(_port);
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
            ExecutorService executor = Executors.newFixedThreadPool(1);
            try {
                while (true) {
                    try {
                        buf.clear();
                        _socket.receive(dp);
                        InetSocketAddress sa = (InetSocketAddress) dp.getSocketAddress();
                        LOG.fine("Pacote recebido (Tamanho: " + dp.getLength() + " Origem: " + dp.getSocketAddress() + ")");

                        InetAddress serverAddress = InetAddress.getByName(ConfiguracaoGlobal.getInstance().getIPServer());

                        // só aceitar pacotes originados do servidor
                        if (sa.getAddress().equals(serverAddress)) {
                            try {
                                this.lePacote(executor, buf);
                            } catch (Throwable t) {
                                LOG.log(Level.SEVERE, "Pacote recebido (Tamanho: " + dp.getLength() + " Origem: " + dp.getSocketAddress() + ")", t);
                            }
                        } else {
                            LOG.warning("Descartando pacote recebido de origem diferente a do controlador: Origem: [" + sa.getAddress().toString() + "] Controlador: [" + ConfiguracaoGlobal.getInstance().getIPServer() + "]");
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
        String serverName = ConfiguracaoGlobal.getInstance().getIPServer();
        int serverPort = ConfiguracaoGlobal.getInstance().getPort();
        DatagramPacket dp = new DatagramPacket(buffer.array(), 0, buffer.position(), new InetSocketAddress(serverName, serverPort));
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
        int timeout = ConfiguracaoGlobal.getInstance().getTimeoutOperacoesUDP();
        boolean ok = false;
        try {
            ok = _latchObterURLs.await(timeout, TimeUnit.SECONDS);
        } catch (InterruptedException e) {
            // nada
        }
        // timeout?
        if (!ok) {
            throw new RuntimeException("Tempo de espera pela resposta (" + timeout + " segundos) esgotado.");
        }
    }
    
    @Override
    protected void preCadastrarPainel() {
        _latchCadastro = new CountDownLatch(1);
    }
    
    @Override
    protected void postCadastrarPainel() {
        int timeout = ConfiguracaoGlobal.getInstance().getTimeoutOperacoesUDP();
        boolean ok = false;
        try {
            ok = _latchCadastro.await(timeout, TimeUnit.SECONDS);
            System.err.println("LATCH CADASTRO OK");
        } catch (InterruptedException e) {
            // nada
        }
        // timeout?
        if (!ok) {
            throw new RuntimeException("Tempo de espera pela resposta (" + timeout + " segundos) esgotado.");
        }
    }

}