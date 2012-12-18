
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

import java.awt.Color;
import java.awt.Font;
import java.awt.Rectangle;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.File;
import java.io.IOException;
import java.util.LinkedList;
import java.util.List;

import javax.swing.ButtonGroup;
import javax.swing.ImageIcon;
import javax.swing.JButton;
import javax.swing.JCheckBox;
import javax.swing.JColorChooser;
import javax.swing.JComboBox;
import javax.swing.JComponent;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JPanel;
import javax.swing.JRadioButton;
import javax.swing.event.ChangeEvent;
import javax.swing.event.ChangeListener;

import br.gov.dataprev.estruturadados.ConfLayout;
import br.gov.dataprev.estruturadados.ConfiguracaoGlobal;
import br.gov.dataprev.painel.audio.AudioPlayer;
import br.gov.dataprev.painel.imagens.ImagesTable;

public class Themes extends JFrame implements ActionListener, ChangeListener
{
	private JLabel display;
	private PainelPane _previewPainelPane;
	
	private int _componenteSelecionado = 0;
	
	private ConfLayout _confLayout = new ConfLayout();
	
	private JComboBox listSound = null;
	private JButton sound = null;
	private JLabel monitor = null;
	private JButton padrao = null;
	private JButton gravar, sair;
	private JRadioButton _monitorPrimarioButton;
	private JRadioButton _monitorSecundarioButton;
	private JCheckBox _protecaoTelaCheckBox;
	private JCheckBox _vocalizarCheckBox;
	
	private static Themes _Instance;
	
	public static Themes getInstance()
	{
		if (_Instance == null)
		{
			_Instance = new Themes();
		}
		return _Instance;
	}
	
	private Themes()
	{
		this.setTitle("Configuração do Painel");
		this.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
		this.setSize(750, 500);
		this.setLocationRelativeTo(null);
		
		JPanel caixa = new JPanel();
		caixa.setLayout(null);
		
		monitor = new JLabel(ImagesTable.getInstance().getImageIcon("display.png"));
		monitor.setBounds(0, 17, 235, 245);
		caixa.add(monitor);
		
		_previewPainelPane = new PainelPane();
		_previewPainelPane.setBounds(17, 33, 197, 141);
		_previewPainelPane.addChangeListener(this);
		caixa.add(_previewPainelPane);
		
		_monitorPrimarioButton = new JRadioButton("Monitor Primário");
		_monitorPrimarioButton.setToolTipText("Define o monitor onde o painel irá aparecer");
		_monitorPrimarioButton.setBounds(new Rectangle(17, 275, 160, 20));
		caixa.add(_monitorPrimarioButton);
		
		_monitorSecundarioButton = new JRadioButton("Monitor Secundário");
		_monitorSecundarioButton.setToolTipText("Define o monitor onde o painel irá aparecer");
		_monitorSecundarioButton.setBounds(new Rectangle(17, 300, 160, 20));
		caixa.add(_monitorSecundarioButton);
		
		_protecaoTelaCheckBox = new JCheckBox("Desativar Proteção de Tela");
		_protecaoTelaCheckBox.setSelected(ConfLayout.getInstance().getDesativarProtecaoTela());
		_protecaoTelaCheckBox.setActionCommand("CheckProtecaoTela");
		_protecaoTelaCheckBox.addActionListener(this);
		_protecaoTelaCheckBox.setToolTipText("Impede que a Proteção de Tela entre enquanto o painel estiver exibindo senhas.");
		_protecaoTelaCheckBox.setBounds(new Rectangle(17, 340, 220, 20));
		caixa.add(_protecaoTelaCheckBox);
		
		JRadioButton rSel = ConfiguracaoGlobal.getInstance().isDualVideo() ? _monitorSecundarioButton : _monitorSecundarioButton;
		rSel.setSelected(true);
		
		ButtonGroup group = new ButtonGroup();
	    group.add(_monitorPrimarioButton);
	    group.add(_monitorSecundarioButton);
		
		
		
		listSound = new JComboBox();
		listSound.setToolTipText("Seleção de toques");
		listSound.setBounds(new Rectangle(17, 385, 140, 20));
		
		List<String> audioAlertList = getAudioAlertList();
		for (String audio : audioAlertList)
		{
			listSound.addItem(audio);
		}
		
		caixa.add(listSound);
		
		ImageIcon img = ImagesTable.getInstance().getImageIcon("play.png");
		sound = new JButton(img);
		sound.setToolTipText("Ouvir toque");
		sound.setBorder(null);
		sound.setBackground(new Color(0xDF, 0xDF, 0xDF));
		sound.setBounds(175, 385, 20, 20);
		sound.addActionListener(new ActionListener()
		{
			public void actionPerformed(ActionEvent e)
			{
				AudioPlayer.getInstance().play(AudioPlayer.ALERTS_PATH, (String) listSound.getSelectedItem());
			}	
		}
		);
		caixa.add(sound);
		
		_vocalizarCheckBox = new JCheckBox("Vocalizar senhas");
		_vocalizarCheckBox.setSelected(ConfLayout.getInstance().isVocalizarSenhas());
		_vocalizarCheckBox.setActionCommand("VocalizarSenhas");
		_vocalizarCheckBox.addActionListener(this);
		_vocalizarCheckBox.setToolTipText("<html>Vocalizar senhas ao chamar.<br/><b><font color=\"red\">EXPERIMENTAL</font></b></html>");
		_vocalizarCheckBox.setBounds(new Rectangle(17, 355, 220, 20));
		caixa.add(_vocalizarCheckBox);
		
		padrao = new JButton();
		padrao.setToolTipText("Restaurar valores padrão");
		padrao.setFont(new Font("Arial", Font.PLAIN, 12));
		padrao.setBounds(new Rectangle(476, 425, 150, 20));
		padrao.setText("Restaurar Padrões");
		padrao.addActionListener(new ActionListener()
		{
			public void actionPerformed(ActionEvent e)
			{
				_confLayout.resetDefaults();
				Themes.this.aplicarLayout();
			}
		});
		caixa.add(padrao);
		
		gravar = new JButton("Gravar");
		gravar.setMnemonic('G');
		gravar.setBounds(new Rectangle(276, 425, 94, 20));
		gravar.addActionListener(new ActionListener()
		{
			public void actionPerformed(ActionEvent e)
			{
				try
				{
					ConfiguracaoGlobal.getInstance().setDualVideo(_monitorSecundarioButton.isSelected());
					ConfiguracaoGlobal.getInstance().salvarConfiguracao();
					Robo robo = Web.getInstance().getRobo();
					if (robo != null)
					{
						robo.setDesativarProtecaoTela(_confLayout.getDesativarProtecaoTela());
					}
					
					_confLayout.setSom((String) listSound.getSelectedItem());
					ConfLayout.getInstance().setSom((String) listSound.getSelectedItem());
					
					ConfLayout.getInstance().setVocalizarSenhas(_confLayout.isVocalizarSenhas());
					
					_confLayout.salvar();
					Web.getInstance().aplicarLayout(_confLayout);
					Mensagem.showMensagem("Configuração modificada com sucesso.", "Layout");
				}
				catch (IOException exc)
				{
					Mensagem.showMensagem("Falha salvando configuração de layout.", "ERRO", 0, exc);
				}
				
			}
		});
		caixa.add(gravar);
		
		this.sair = new JButton("Sair");
		this.sair.setMnemonic('S');
		this.sair.setBounds(new Rectangle(376, 425, 94, 20));
		this.sair.addActionListener(new ActionListener()
		{
			public void actionPerformed(ActionEvent e)
			{
				Themes.this.setVisible(false);
			}
		});
		caixa.add(sair);
		
		display = new JLabel();
		display.setText("");
		display.setBounds(240, 10, 196, 16);
		caixa.add(display);
		
		final JColorChooser cc = new JColorChooser();
		cc.setBounds(240, 30, (int) cc.getPreferredSize().getWidth(), (int) cc.getPreferredSize().getHeight());
		cc.setPreviewPanel(new JPanel());
		cc.getSelectionModel().addChangeListener(
				new ChangeListener()
				{
					
					@Override
					public void stateChanged(ChangeEvent e)
					{
						Color c = cc.getColor();
						switch (_componenteSelecionado)
						{
							case 0:
								_confLayout.setCorFundo(c);
								break;
							case 1:
								_confLayout.setCorMsgEspecial(c);
								break;
							case 2:
								_confLayout.setCorSenha(c);
								break;
							case 3:
								_confLayout.setCorGuiche(c);
								//guiche.setForeground(cor.getBackground());
								//numGuiche.setForeground(cor.getBackground());
								break;
							default:
								_confLayout.setCorFundo(c);
								//backgroundColor.setBackground(cor.getBackground());
								break;
						}
						Themes.this.aplicarLayout();
					}
				}
		);
		caixa.add(cc);
		
		this.aplicarLayout();
		//carregaCores();
		
		caixa.setVisible(true);
		
		this.setContentPane(caixa);
		//this.setAlwaysOnTop(true);
	}
	
	private void aplicarLayout()
	{
		_previewPainelPane.aplicarLayout(_confLayout);
		
		for (int i = 0; i < listSound.getItemCount(); i++)
		{
			this.listSound.getItemAt(i).equals(_confLayout.getSom());
		}
	}

	/* (non-Javadoc)
	 * @see java.awt.event.ActionListener#actionPerformed(java.awt.event.ActionEvent)
	 */
	@Override
	public void actionPerformed(ActionEvent e)
	{
		String cmd = e.getActionCommand();
		
		if (cmd.equals("CheckProtecaoTela"))
		{
			_confLayout.setDesativarProtecaoTela(_protecaoTelaCheckBox.isSelected());
		}
		else if (cmd.equals("VocalizarSenhas"))
		{
			_confLayout.setVocalizarSenhas(_vocalizarCheckBox.isSelected());
		}
	}

	/* (non-Javadoc)
	 * @see javax.swing.event.ChangeListener#stateChanged(javax.swing.event.ChangeEvent)
	 */
	@Override
	public void stateChanged(ChangeEvent e)
	{
		JComponent source = (JComponent) e.getSource();
		
		if (source == _previewPainelPane.getLabelMsgEspecial())
		{
			this.display.setText("COR DE MENSAGEM ESPECIAL");
			_componenteSelecionado = 1;
		}
		else if (source == _previewPainelPane.getLabelSenha())
		{
			this.display.setText("COR DE SENHA");
			_componenteSelecionado = 2;
		}
		else if (source == _previewPainelPane.getLabelGuiche())
		{
			this.display.setText("COR DE GUICHÊ");
			_componenteSelecionado = 3;
		}
		else
		{
			this.display.setText("COR DE FUNDO");
			_componenteSelecionado = 0;
		}
	}
	
	public List<String> getAudioAlertList()
	{
		List<String> list = new LinkedList<String>();
		
		File alertsDir = new File(AudioPlayer.ALERTS_PATH);
		
		for (File f : alertsDir.listFiles())
		{
			if (f.isFile() && f.getName().toLowerCase().endsWith(".wav"))
			{
				list.add(f.getName());
			}
		}
		
		return list;
	}
}
