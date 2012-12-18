
/**
 * 
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro dos termos da Licença Pública Geral GNU como 
 * publicada pela Fundação do Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt", junto com este programa, se não, escreva para a 
 * Fundação do Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 *
**/

package br.gov.dataprev.controladorpainel.recebidos;

import java.net.InetSocketAddress;
import java.nio.ByteBuffer;

import br.gov.dataprev.controladorpainel.ConfigManager;
import br.gov.dataprev.controladorpainel.GerenciadorPaineis;
import br.gov.dataprev.controladorpainel.Painel;
import br.gov.dataprev.controladorpainel.enviados.ConfirmaMsg;

/**
 * Mensagem recebida de um Painel que sinaliza que aquele painel está vivo
 * 
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class PainelVivo extends ClienteMsg
{

	private int _intervalo;

	/**
	 * @param origem
	 */
	public PainelVivo(InetSocketAddress origem)
	{
		super(origem);
	}

	/* (non-Javadoc)
	 * @see br.gov.dataprev.painelserver.recebidos.MsgRecebida#processa()
	 */
	@Override
	protected void processa()
	{
		Painel painel = GerenciadorPaineis.getInstance().getPainelPorHost(this.getHostRemoto());
		if (painel != null)
		{
			painel.marcaContatoAgora();
			
			if (_intervalo != ConfigManager.getInstance().getTimeoutPainel())
			{
				ConfirmaMsg cm = new ConfirmaMsg(painel);
				cm.envia();
			}
		}
	}

	/* (non-Javadoc)
	 * @see br.gov.dataprev.painelserver.recebidos.MsgRecebida#readDados(java.nio.ByteBuffer)
	 */
	@Override
	protected void readDados(ByteBuffer buf)
	{
		_intervalo = ClienteMsg.getShort(buf);
	}
}
