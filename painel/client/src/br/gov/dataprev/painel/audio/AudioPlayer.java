
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

package br.gov.dataprev.painel.audio;

import java.io.File;
import java.io.IOException;
import java.util.logging.Logger;

import javax.sound.sampled.AudioFormat;
import javax.sound.sampled.AudioInputStream;
import javax.sound.sampled.AudioSystem;
import javax.sound.sampled.DataLine;
import javax.sound.sampled.LineUnavailableException;
import javax.sound.sampled.SourceDataLine;
import javax.sound.sampled.UnsupportedAudioFileException;

/**
 * @author ulysses
 *
 */
public class AudioPlayer
{
	private static final Logger LOG = Logger.getLogger(AudioPlayer.class.getName());
	
	private static AudioPlayer _Instance;
	
	public static final String ALERTS_PATH = "sons/alertas/";
	
	private static final int BUFFER_SIZE = 64*1024;
	
	private final Vocalizador _vocalizador = new Vocalizador();
	
	public static AudioPlayer getInstance()
	{
		if (_Instance == null)
		{
			_Instance = new AudioPlayer();
		}
		return _Instance;
	}
	
	private AudioPlayer()
	{
	}
	
	public void playAndWait(String baseDir, String filename)
	{
		this.play(baseDir, filename, true);
	}
	
	public void play(String baseDir, String filename)
	{
		this.play(baseDir, filename, false);
	}
	
	public void play(String baseDir, String filename, boolean wait)
	{	
		this.play(new File(baseDir, filename), wait);
	}
	
	public void play(File f, boolean wait)
	{
		LOG.info("Playing ("+f.getAbsolutePath()+")...");
		
		if (!f.exists())
		{
			LOG.severe("Erro ao tocar ("+f.getAbsolutePath()+", arquivo não existe.");
		}
		else
		{
			Playback playback = new Playback(f);
			
			if (wait)
			{
				// executa run neste thread
				playback.run();
			}
			else
			{
				// async
				playback.start();
			}
		}
	}

	/**
	 * @return the vocalizador
	 */
	public Vocalizador getVocalizador()
	{
		return _vocalizador;
	}

	class Playback extends Thread
	{
		private final File _file;
		
		public Playback(File file)
		{
			_file = file;
		}

		@Override
		public void run()
		{
			AudioInputStream audioInputStream = null;
			try
			{
				audioInputStream = AudioSystem.getAudioInputStream(_file);
			}
			catch (UnsupportedAudioFileException e1)
			{
				e1.printStackTrace();
				return;
			}
			catch (IOException e1)
			{
				e1.printStackTrace();
				return;
			}
			
			AudioFormat format = audioInputStream.getFormat();
			SourceDataLine auline = null;
			DataLine.Info info = new DataLine.Info(SourceDataLine.class, format);
			
			try
			{
				auline = (SourceDataLine) AudioSystem.getLine(info);
				auline.open(format);
			}
			catch (LineUnavailableException e)
			{
				e.printStackTrace();
				return;
			}
			catch (Exception e)
			{
				e.printStackTrace();
				return;
			}
			
			auline.start();
			int nBytesRead = 0;
			byte[] abData = new byte[BUFFER_SIZE];
			
			try
			{
				while (nBytesRead != -1)
				{
					nBytesRead = audioInputStream.read(abData, 0, abData.length);
					if (nBytesRead >= 0)
						auline.write(abData, 0, nBytesRead);
				}
			}
			catch (IOException e)
			{
				e.printStackTrace();
				return;
			}
			finally
			{
				auline.drain();
				auline.close();
			}
		}
		
		
	}
}
