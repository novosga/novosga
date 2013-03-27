/**
 * 
 */
package org.novosga.painel.server.task;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;

/**
 * @author ulysses
 *
 */
public class AgendadorTarefas
{
	private final ScheduledExecutorService _ses =  Executors.newScheduledThreadPool(2);
	
	private static AgendadorTarefas _Instance;
	
	public static AgendadorTarefas getInstance()
	{
		if (_Instance == null)
		{
			_Instance = new AgendadorTarefas();
		}
		return _Instance;
	}
	
	// Impede construcao direta
	private AgendadorTarefas()
	{
		
	}
	
	public ScheduledExecutorService getSes()
	{
		return _ses;
	}
}
