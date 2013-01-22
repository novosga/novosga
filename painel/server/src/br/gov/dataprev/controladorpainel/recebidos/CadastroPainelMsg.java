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
import java.nio.ByteBuffer;

import br.gov.dataprev.controladorpainel.GerenciadorPaineis;
import br.gov.dataprev.controladorpainel.server.PacketHandler;

/**
 * Mensagem recebida quando um painel deseja se cadastrar neste servidor para
 * receber chamadas(senhas).
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class CadastroPainelMsg extends ClienteMsg {

    private int _idUnidade;
    private byte[] _servicos;
    private int _versaoProtocolo;

    /**
     * @param origem
     */
    public CadastroPainelMsg(InetSocketAddress origem) {
        super(origem);
    }

    /* (non-Javadoc)
     * @see br.gov.dataprev.painelserver.recebidos.MensagemRecebida#readDados(java.nio.ByteBuffer)
     */
    @Override
    protected void readDados(ByteBuffer buf) {
        _versaoProtocolo = buf.getInt();
        _idUnidade = buf.getInt();
        int qtde = ClienteMsg.getByte(buf);
        _servicos = new byte[qtde];
        for (int i = 0; i < qtde; i++) {
            _servicos[i] = (byte) ClienteMsg.getByte(buf);
        }
    }

    /* (non-Javadoc)
     * @see br.gov.dataprev.painelserver.recebidos.MensagemRecebida#processa()
     */
    @Override
    protected void processa() {
        if (_versaoProtocolo != PacketHandler.VERSAO_PROTOCOLO) {
            LOG.warning("Versão de protocolo diferente: Origem: " + this.getHostRemotoStr() + " Protocolo: " + _versaoProtocolo + ". Versão suportada pelo controlador: " + PacketHandler.VERSAO_PROTOCOLO);
        } else {
            GerenciadorPaineis.getInstance().cadastrarPainel(_idUnidade, this.getHostRemoto(), _servicos);
        }
    }
}
