/**
 * 
 */
package br.gov.dataprev.controladorpainel.server;

import java.util.concurrent.CountDownLatch;

import br.gov.dataprev.controladorpainel.enviados.ServerMsg;

/**
 * @author ulysses
 *
 */
public abstract class UDPServer
{
	private final PacketHandler _pHandler;
	
	private final CountDownLatch _latch = new CountDownLatch(1);
	
	private ServerStatus _status = ServerStatus.STOPPED;
	
	private static UDPServer _Instance;
	
	public static UDPServer getInstance()
	{
		if (_Instance == null)
		{
			if (true)
			{
				_Instance = new UDPSimpleListener();
			}
			else
			{
				_Instance = new UDPNIOServer();
			}	
		}
		return _Instance;
	}
	
	public UDPServer(PacketHandler pHandler)
	{
		_pHandler = pHandler;
	}
	
	public abstract void start();
	
	public void aguardaInicio()
	{
		try
		{
			_latch.await();
		}
		catch (InterruptedException e)
		{
			
		}
	}
	
	protected void setStatus(ServerStatus status)
	{
		if (status == ServerStatus.RUNNING)
		{
			_latch.countDown();
		}
		_status = status;
	}
	
	public ServerStatus getStatus()
	{
		return _status;
	}
	
	protected PacketHandler getPacketHandler()
	{
		return _pHandler;
	}
	
	public abstract void envia(ServerMsg msg);
	
}
