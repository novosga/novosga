
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

package br.gov.dataprev.estruturadados;

import java.awt.Color;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Properties;
import java.util.logging.Logger;

import br.gov.dataprev.exec.Painel;
import br.gov.dataprev.painel.audio.AudioPlayer;

public class ConfLayout
{
	private static final Logger LOG = Logger.getLogger(ConfLayout.class.getName());
	
	private static final String ARQUIVO_CONFIG_LAYOUT = "layout.conf";
	
	private static final String DEFAULT_ALERT = "alert.wav";
	
	private static ConfLayout _Instance;
	
	public static ConfLayout getInstance()
	{
		if (_Instance == null)
		{
			_Instance = new ConfLayout();
		}
		return _Instance;
	}
	
	private String _som;
	private Color _corFundo, _corMsgEspecial, _corSenha, _corGuiche;

	private boolean _desativarProtecaoTela;
	private boolean _vocalizarSenhas;
	
	public ConfLayout()
	{
		this.carregaConfiguracao();
	}
	
	private void carregaConfiguracao()
	{
		Properties config = new Properties();
		try
		{
			config.load(new FileInputStream(new File(Painel.getWorkingDirectory(), ARQUIVO_CONFIG_LAYOUT)));
			this.setConfiguracoes(config);
		}
		catch (IOException e)
		{
			LOG.info("Configuração de Layout não encontrada, gerando padrão.");
			// primeira execução, irá usar os defaults
			this.setConfiguracoes(config);
			try
			{
				this.salvar();
			}
			catch (IOException e1)
			{
				LOG.warning("Não foi possivel salvar a configuração padrão do layout.");
			}
		}
	}
	
	public void resetDefaults()
	{
		this.setConfiguracoes(new Properties());
	}
	
	private void setConfiguracoes(Properties config)
	{
		this.setCorFundo(Integer.decode(config.getProperty("CorFundo", "0x000000")));
		this.setCorMsgEspecial(Integer.decode(config.getProperty("CorMsgEspecial", "0xFFFFFF")));
		this.setCorSenha(Integer.decode(config.getProperty("CorSenha", "-256")));
		this.setCorGuiche(Integer.decode(config.getProperty("CorGuiche", "0xFFFFFF")));
		this.setSom(config.getProperty("Som", ConfLayout.DEFAULT_ALERT));
		this.setDesativarProtecaoTela(Boolean.parseBoolean(config.getProperty("DesativarProtecaoTela", "true")));
		this.setVocalizarSenhas(Boolean.parseBoolean(config.getProperty("VocalizarSenhas", "false")));
	}
	
	public Color getCorFundo()
	{
		return _corFundo;
	}
	
	public void setCorFundo(int corFundo)
	{
		this.setCorFundo(new Color(corFundo));
	}
	
	public void setCorFundo(Color corFundo)
	{
		_corFundo = corFundo;
	}
	
	public Color getCorMsgEspecial()
	{
		return _corMsgEspecial;
	}
	
	public void setCorMsgEspecial(int corMsgEspecial)
	{
		this.setCorMsgEspecial(new Color(corMsgEspecial));
	}
	
	public void setCorMsgEspecial(Color corMsgEspecial)
	{
		_corMsgEspecial = corMsgEspecial;
	}
	
	public Color getCorSenha()
	{
		return _corSenha;
	}
	
	public void setCorSenha(int corSenha)
	{
		this.setCorSenha(new Color(corSenha));
	}
	
	public void setCorSenha(Color corSenha)
	{
		_corSenha = corSenha;
	}
	
	public Color getCorGuiche()
	{
		return _corGuiche;
	}
	
	public void setCorGuiche(int corGuiche)
	{
		_corGuiche = new Color(corGuiche);
	}
	
	public void setCorGuiche(Color corGuiche)
	{
		_corGuiche = corGuiche;
	}
	
	public String getSom()
	{
		return _som;
	}
	
	public void setSom(String som)
	{
		_som = som;
	}

	/**
	 * @throws IOException 
	 * @throws FileNotFoundException 
	 * 
	 */
	public void salvar() throws FileNotFoundException, IOException
	{
		Properties properties = new Properties();
		
		properties.setProperty("CorFundo", String.valueOf(this.getCorFundo().getRGB()));
		properties.setProperty("CorMsgEspecial", String.valueOf(this.getCorMsgEspecial().getRGB()));
		properties.setProperty("CorSenha", String.valueOf(this.getCorSenha().getRGB()));
		properties.setProperty("CorGuiche", String.valueOf(this.getCorGuiche().getRGB()));
		properties.setProperty("Som", this.getSom());
		properties.setProperty("DesativarProtecaoTela", String.valueOf(this.getDesativarProtecaoTela()));
		properties.setProperty("VocalizarSenhas", String.valueOf(this.isVocalizarSenhas()));
		
		properties.store(new FileOutputStream(new File(Painel.getWorkingDirectory(), ARQUIVO_CONFIG_LAYOUT)), "Configurações Layout do Painel");
	}

	public void setDesativarProtecaoTela(boolean b)
	{
		_desativarProtecaoTela = b;
	}
	
	public boolean getDesativarProtecaoTela()
	{
		return _desativarProtecaoTela;
	}

	/**
	 * @param vocalizarSenhas the vocalizarSenhas to set
	 */
	public void setVocalizarSenhas(boolean vocalizarSenhas)
	{
		_vocalizarSenhas = vocalizarSenhas;
	}

	/**
	 * @return the vocalizarSenhas
	 */
	public boolean isVocalizarSenhas()
	{
		return _vocalizarSenhas;
	}
}
