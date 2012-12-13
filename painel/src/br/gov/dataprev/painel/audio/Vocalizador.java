/**
 * 
 */
package br.gov.dataprev.painel.audio;

import java.io.File;
import java.util.logging.Logger;

/**
 * @author ulysses
 *
 */
public class Vocalizador
{
	private static final Logger LOG = Logger.getLogger(Vocalizador.class.getName());
	
	public static final String ARQUIVOS_VOCALIZACAO = "sons/vocalizacao/";
	
	public void vocalizar(String str, boolean wait) throws Exception
	{
		str = str.toLowerCase();
		
		File f = new File(ARQUIVOS_VOCALIZACAO, str+".wav");
		if (!f.exists())
		{
			throw new Exception("Impossivel vocalizar "+str+", o arquivo ("+f.getAbsolutePath()+") não existe.");
		}
		else
		{
			AudioPlayer.getInstance().play(f, wait);
		}
	}
}
