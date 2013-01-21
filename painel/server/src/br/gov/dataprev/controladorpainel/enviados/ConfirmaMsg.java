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
package br.gov.dataprev.controladorpainel.enviados;

import java.nio.ByteBuffer;

import br.gov.dataprev.controladorpainel.ConfigManager;
import br.gov.dataprev.controladorpainel.Painel;
import br.gov.dataprev.controladorpainel.server.PacketServer;

/**
 * Mensagem enviada ao Painel para confirmações (propósito geral).
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class ConfirmaMsg extends ServerMsg {

    /**
     * @param p
     */
    public ConfirmaMsg(Painel p) {
        super(p);
    }

    /* (non-Javadoc)
     * @see br.gov.dataprev.painelserver.enviados.ServerMsg#writeDataTo(java.nio.ByteBuffer)
     */
    @Override
    protected void writeDataTo(ByteBuffer buf) {
        buf.put((byte) 1);
        buf.putShort((short) ConfigManager.getInstance().getTimeoutPainel());
    }
}
