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
package br.gov.dataprev.controladorpainel;

import java.net.InetAddress;
import java.net.InetSocketAddress;
import java.util.Arrays;
import java.util.logging.Logger;

import br.gov.dataprev.controladorpainel.enviados.ConfirmaMsg;

/**
 * Representa um painel em uma unidade.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class Painel {

    private static final Logger LOG = Logger.getLogger(Painel.class.getName());
    public static final int PORT = 8888;
    private int _apsId;
    private final InetSocketAddress _host;
    private byte[] _servicos;
    private long _expira;
    private final PainelDAO _painelDAO;

    public Painel(int apsId, InetAddress host, byte[] servicos) {
        this(apsId, host, servicos, false);
    }

    public Painel(int apsId, InetAddress host, byte[] servicos, boolean existeNoBanco) {
        _apsId = apsId;
        _host = new InetSocketAddress(host, PORT);
        _painelDAO = new PainelDAO(this, existeNoBanco);
        this.setServicos(servicos);
        if (existeNoBanco) {
            this.marcaContatoAgora();
        }
    }

    public void setServicos(byte[] servicos) {
        _servicos = servicos;
        Arrays.sort(_servicos);
    }

    public byte[] getServicos() {
        byte[] original = _servicos;

        return Arrays.copyOf(original, original.length);
    }

    public boolean seInteressaPorServico(int id_serv) {
        return Arrays.binarySearch(_servicos, (byte) id_serv) >= 0;
    }

    public void setApsId(int apsId) {
        _apsId = apsId;
    }

    public int getApsId() {
        return _apsId;
    }

    public InetSocketAddress getSocketAddress() {
        return _host;
    }

    public String getSocketAddressStr() {
        return _host.getAddress().getHostAddress() + ':' + _host.getPort();
    }

    public int getIntHost() {
        byte[] host = _host.getAddress().getAddress();
        int longip = host[3] & 0xFF;
        longip |= (host[2] & 0xFF) << 8;
        longip |= (host[1] & 0xFF) << 16;
        longip |= (host[0] & 0xFF) << 24;
        return longip;
    }

    public void marcaContatoAgora() {
        LOG.fine("MARCA CONTATO: " + this);
        _expira = System.currentTimeMillis() + ConfigManager.getInstance().getTimeoutPainel() * 1000;
    }

    public boolean expirou() {
        return System.currentTimeMillis() > _expira;
    }

    public long segundosExpirados() {
        return (System.currentTimeMillis() - _expira) / 1000;
    }

    public void enviarMsgConfirmacao() {
        ConfirmaMsg cm = new ConfirmaMsg(this);
        cm.envia();
    }

    public void salvar() {
        _painelDAO.salvar();
    }

    public void removerDoBanco() {
        _painelDAO.removerDoBanco();
    }

    @Override
    public String toString() {
        StringBuilder sb = new StringBuilder();
        byte[] servicos = _servicos;
        int i = 0;
        sb.append('(');
        for (; i < servicos.length - 1; i++) {
            sb.append(String.valueOf(servicos[i] & 0xFF));
            sb.append(" | ");
        }
        // evitar ArrayIndexOutOfBoundsException com arrays vazios
        if (i < servicos.length) {
            sb.append(String.valueOf(servicos[i] & 0xFF));
            sb.append(')');
        }
        return "[Painel: " + this.getSocketAddressStr() + " - Servico: " + sb.toString() + "]";
    }

    /**
     *
     */
    public void desativar() {
        _expira = 0;
    }
}
