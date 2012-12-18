
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

import java.awt.AWTException;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.TrayIcon;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.util.logging.Logger;

import javax.swing.JOptionPane;

import br.gov.dataprev.painel.imagens.ImagesTable;

/**
 * @author DATAPREV
 * @version 1.0
 * @category Interface
 */
public class SysTray implements ActionListener
{
	private static final Logger LOG = Logger.getLogger(SysTray.class.getName());
	
	public static final String VERSAO = "1.0.0";
	
	/**
	 * 
	 * @throws AWTException
	 */
	public SysTray()
	{
	
		if (SystemTray.isSupported())
		{
			LOG.fine("O sistema possui suporte a ícone de bandeja.");
			
			SystemTray sys = SystemTray.getSystemTray();
			
			// cria o menu popup
			PopupMenu popup = new PopupMenu();
			
			MenuItem miExibe = new MenuItem("Exibir Painel");
			miExibe.setActionCommand("exibir");
			miExibe.addActionListener(this);
			popup.add(miExibe);
			
			popup.addSeparator();
			
			MenuItem miConf = new MenuItem("Configurar Serviços");
			miConf.setActionCommand("configurar");
			miConf.addActionListener(this);
			popup.add(miConf);
			
			MenuItem miConfLay = new MenuItem("Configurar Layout");
			miConfLay.setActionCommand("layout");
			miConfLay.addActionListener(this);
			popup.add(miConfLay);
			
			popup.addSeparator();
			
			MenuItem miSobre = new MenuItem("Sobre");
			miSobre.setActionCommand("sobre");
			miSobre.addActionListener(this);
			popup.add(miSobre);
			
			popup.addSeparator();
			
			MenuItem miSair = new MenuItem("Sair");
			miSair.setActionCommand("sair");
			miSair.addActionListener(this);
			popup.add(miSair);
			
			// constroi o system tray
			TrayIcon trayIcon = new TrayIcon(ImagesTable.SGA_ICON.getImage(), "Painel SGA", popup);
			
			// Ajusta ao tamanho do respectivo Sistema Operacional automaticamente
			trayIcon.setImageAutoSize(true);
			
			// adiciona imagem do system tray
			try
			{
				sys.add(trayIcon);
				LOG.fine("Ícone de bandeja exibido com sucesso.");
			}
			catch (AWTException e)
			{
				Mensagem.showMensagem("Falha ao adicionar o Ícone na bandeja.\nDetalhe: "+e.getMessage(), "Erro", 0);
				System.exit(1);
			}
		}
		else
		{
			Mensagem.showMensagem("Seu sistema não suporta Ícone de bandeja.", "Erro", 0);
			System.exit(1);
		}
	}

	@Override
	public void actionPerformed(ActionEvent e)
	{
		String cmd = e.getActionCommand();
		if (cmd.equals("configurar"))
		{
			CPanel.getInstance().setVisible(true);
		}
		else if (cmd.equals("layout"))
		{
			Themes.getInstance().setVisible(true);
		}
		else if (cmd.equals("exibir"))
		{
			Web.getInstance().setVisible(true);
		}
		else if (cmd.equals("sobre"))
		{
			String title = "Sobre o Painel SGA Livre";
			String msg = "Painel SGA Livre - Versão: "+SysTray.VERSAO+"\n";
			msg += "Software do sistema SGA\n\n";
			msg += "DATAPREV - 2009";
			JOptionPane.showMessageDialog(null, msg, title, JOptionPane.INFORMATION_MESSAGE, ImagesTable.SGA_ICON);
		}
		else if (cmd.equals("sair"))
		{
			System.exit(0);
		}
	}
}
