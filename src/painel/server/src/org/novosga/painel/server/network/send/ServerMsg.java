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
package org.novosga.painel.server.network.send;

import org.novosga.painel.server.Main;
import java.io.UnsupportedEncodingException;
import java.net.InetSocketAddress;
import java.nio.ByteBuffer;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.novosga.painel.server.NetMsg;
import org.novosga.painel.server.Painel;
import org.novosga.painel.server.network.PacketServer;

/**
 * Classe base das respostas do servidor
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public abstract class ServerMsg extends NetMsg {

    private static final Logger LOG = Logger.getLogger(ServerMsg.class.getName());
    
    private final PacketServer _server;

    /**
     * @param address
     */
    public ServerMsg(InetSocketAddress address) {
        super(address);
        _server = Main.getServer();
    }

    public ServerMsg(Painel p) {
        this(p.getSocketAddress());
    }

    public final boolean writeTo(ByteBuffer buf) {
        try {
            this.writeDataTo(buf);
            buf.limit(buf.position());
            return true;
        } catch (Throwable t) {
            LOG.log(Level.SEVERE, "Erro escrevendo conteudo do pacote a ser enviado: " + this, t);
            return false;
        }
    }

    protected abstract void writeDataTo(ByteBuffer buf);

    public void envia() {
        LOG.fine("SEND: " + this);
        _server.envia(this);
    }

    @Override
    public String toString() {
        return "[" + this.getClass().getSimpleName() + " Destino: " + this.getHostRemotoStr() + "]";
    }

    public static void writeString(ByteBuffer buf, String str) {
        byte[] data;
        try {
            data = str.getBytes("ISO-8859-1");
        } catch (UnsupportedEncodingException e) {
            data = str.getBytes();
            LOG.log(Level.WARNING, "Problema codificando string, CharSet ISO-8859-1 não suportado, acentos podem apresentar problemas.", e);
        }
        buf.put(data);
        buf.put((byte) 0);
    }
}
