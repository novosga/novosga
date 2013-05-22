/**
 * 
 */
package org.novosga.painel.server.task;

import java.util.Collection;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.novosga.painel.server.ConfigManager;
import org.novosga.painel.server.GerenciadorPaineis;
import org.novosga.painel.server.Painel;

/**
 * @author ulysses
 *
 */
public class LimparPaineisInativos implements Runnable
{
	private static final Logger LOG = Logger.getLogger(LimparPaineisInativos.class.getName());
	
	@Override
	public void run()
	{
		try
		{
			int time = ConfigManager.getInstance().getRemoverPaineisIntervalo();
			Collection<Painel> paineis = GerenciadorPaineis.getInstance().getPaineis();
			
			int removidos = 0;
			for (Painel p : paineis)
			{
				if (p.segundosExpirados() > time)
				{
					LOG.fine("LimparPaineisInativos: removendo: "+p);
					GerenciadorPaineis.getInstance().removerPainel(p);
					LOG.fine("LimparPaineisInativos: removendo do banco: "+p);
					p.removerDoBanco();
					LOG.finest("LimparPaineisInativos: sucesso: removido: "+p);
					removidos++;
				}
			}
			LOG.info("Tarefa de limpeza de paineis inativos removeu "+removidos+" paineis");
		}
		catch (Throwable t)
		{
			LOG.log(Level.SEVERE, "Erro durante execução da tarefa de limpeza de paineis inativos.", t);
		}
	}
	
}
