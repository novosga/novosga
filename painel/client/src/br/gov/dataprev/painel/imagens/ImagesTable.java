
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

package br.gov.dataprev.painel.imagens;

import java.awt.Image;
import java.util.HashMap;

import javax.swing.ImageIcon;

/**
 * @author ulysses
 *
 */
public class ImagesTable
{
	private static final ImagesTable INSTANCE = new ImagesTable();
	
	public static final ImageIcon SGA_ICON = ImagesTable.getInstance().getImageIcon("tray.png");
	public static final ImageIcon INFO_ICON = ImagesTable.getInstance().getImageIcon("informacao.png");
	public static final ImageIcon ALERTA_ICON = ImagesTable.getInstance().getImageIcon("alerta.png");
	public static final ImageIcon ERRO_ICON = ImagesTable.getInstance().getImageIcon("erro.png");
	
	private HashMap<String, ImageIcon> _imagesTable = new HashMap<String, ImageIcon>();
	
	public static ImagesTable getInstance()
	{
		return INSTANCE;
	}
	
	private ImagesTable()
	{
		
	}
	
	public ImageIcon getImageIcon(String nome)
	{
		ImageIcon imageIcon = _imagesTable.get(nome);
		if (imageIcon == null)
		{
			imageIcon = new ImageIcon(ImagesTable.class.getResource(nome));
			_imagesTable.put(nome, imageIcon);
		}
		return imageIcon;
	}
	
	public Image getImage(String nome)
	{
		return this.getImageIcon(nome).getImage();
	}
	
	public Image getImage(String nome, int width, int height)
	{
		return this.getImage(nome).getScaledInstance(width, height, Image.SCALE_SMOOTH);
	}
	
	public ImageIcon getImageIcon(String nome, int width, int height)
	{
		return new ImageIcon(this.getImage(nome).getScaledInstance(width, height, Image.SCALE_SMOOTH));
	}
}
