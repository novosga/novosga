
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

package br.gov.dataprev.userinterface;

import javax.swing.JOptionPane;
import br.gov.dataprev.painel.imagens.ImagesTable;
import java.io.PrintWriter;
import java.io.StringWriter;

public class Mensagem
{
	
	public static void showMensagem(String mensagem, String title)
	{
		Mensagem.showMensagem(mensagem, title, 2, null);
	}
	
	private static String getImagemNome(int i)
	{
		switch (i)
		{
			case 0:
				return "erro.png";
			case 1:
				return "alerta.png";
			case 2:
				return "informacao.png";
		}
		return null;
	}
	
	public static void showMensagem(String mensagem, String title, int imagem)
	{
		Mensagem.showMensagem(mensagem, title, imagem, null);
	}
	
	public static void showMensagem(String mensagem, String title, int imagem, Throwable t)
	{
		String imgNome = Mensagem.getImagemNome(imagem);
		
		if (t != null && System.getProperty("args").contains("-debug"))
		{
			StringWriter sw = new StringWriter();
			t.printStackTrace(new PrintWriter(sw));
			
			mensagem += "\n\nInformações Técnicas:\n"+sw.toString();
		}
		
		JOptionPane.showMessageDialog(null, mensagem, title, JOptionPane.INFORMATION_MESSAGE, ImagesTable.getInstance().getImageIcon(imgNome));
	}
}
