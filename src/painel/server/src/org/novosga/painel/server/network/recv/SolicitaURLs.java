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
package org.novosga.painel.server.network.recv;

import java.net.InetSocketAddress;
import java.nio.ByteBuffer;

import org.novosga.painel.server.network.send.InformaURLsMsg;

/**
 * Mensagem recebida de um cliente que deseja obter as URLs com as listas de
 * unidades e servicos
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class SolicitaURLs extends ClienteMsg {

    /**
     * @param origem
     */
    public SolicitaURLs(InetSocketAddress origem) {
        super(origem);
    }

    /* (non-Javadoc)
     * @see br.gov.dataprev.painelserver.recebidos.ClienteMsg#readDados(java.nio.ByteBuffer)
     */
    @Override
    protected void readDados(ByteBuffer buf) {
        // o primeiro byte sinalizando que era um pacote de SolicitaURLs já foi lido
        // nada mais a ser lido
    }

    /* (non-Javadoc)
     * @see br.gov.dataprev.painelserver.recebidos.ClienteMsg#processa()
     */
    @Override
    protected void processa() {
        InformaURLsMsg um = new InformaURLsMsg(this.getSocketAddress());
        um.envia();
    }
}
