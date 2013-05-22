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

import java.nio.ByteBuffer;
import org.novosga.painel.server.Painel;

/**
 * Mensagem enviada para o Painel para chamar uma senha.
 *
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class SenhaMsg extends ServerMsg {

    private final String _msgEspecial;
    private final char _codServ;
    private final int _senha;
    private final String _guicheStr;
    private final int _guiche;

    /**
     * @param p
     */
    public SenhaMsg(Painel p, String msgEspecial, char codServ, int senha, String guicheStr, int guiche) {
        super(p);
        _msgEspecial = msgEspecial;
        _codServ = codServ;
        _senha = senha;
        _guicheStr = guicheStr;
        _guiche = guiche;
    }

    /* (non-Javadoc)
     * @see br.gov.dataprev.painelserver.enviados.ServerMsg#writeDataTo(java.nio.ByteBuffer)
     */
    @Override
    public void writeDataTo(ByteBuffer buf) {
        buf.put((byte) 0);
        ServerMsg.writeString(buf, _msgEspecial);
        buf.put((byte) _codServ);
        buf.putShort((short) _senha);

        ServerMsg.writeString(buf, _guicheStr);
        buf.put((byte) _guiche);
    }
}
