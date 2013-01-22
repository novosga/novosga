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
package br.gov.dataprev.controladorpainel.recebidos;

import java.net.InetSocketAddress;
import java.nio.BufferUnderflowException;
import java.nio.ByteBuffer;
import java.util.logging.Level;
import java.util.logging.Logger;

import br.gov.dataprev.controladorpainel.NetMsg;

/**
 * Classe base das mensagens recebidas do cliente.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public abstract class ClienteMsg extends NetMsg implements Runnable {

    public static final Logger LOG = Logger.getLogger(ClienteMsg.class.getName());

    public ClienteMsg(InetSocketAddress origem) {
        super(origem);
    }

    public final boolean read(ByteBuffer buf) {
        try {
            this.readDados(buf);
            return true;
        } catch (BufferUnderflowException e) {
            LOG.log(Level.WARNING, "Dados insuficientes tentando ler pacote: " + this, e);
            return false;
        } catch (Throwable t) {
            LOG.log(Level.SEVERE, "Error lendo dados do pacote: " + this, t);
            return false;
        }
    }

    protected abstract void readDados(ByteBuffer buf);

    /* (non-Javadoc)
     * @see java.lang.Runnable#run()
     */
    @Override
    public void run() {
        try {
            this.processa();
        } catch (Throwable t) {
            LOG.log(Level.SEVERE, "Falha processando: " + this.getClass().getSimpleName(), t);
        }
    }

    /**
     *
     */
    protected abstract void processa();

    @Override
    public String toString() {
        return "[" + this.getClass().getSimpleName() + " - Origem: " + this.getHostRemotoStr() + "]";
    }

    public static int getByte(ByteBuffer buf) {
        return buf.get() & 0xFF;
    }

    public static int getShort(ByteBuffer buf) {
        return buf.getShort() & 0xFFFF;
    }
}
