/**
 * 
 */
package br.gov.dataprev.controladorpainel.server;

import java.io.IOException;
import java.net.DatagramPacket;
import java.net.DatagramSocket;
import java.net.InetSocketAddress;
import java.net.SocketException;
import java.nio.ByteBuffer;
import java.util.logging.Level;
import java.util.logging.Logger;

import br.gov.dataprev.controladorpainel.enviados.ServerMsg;

/**
 * @author ulysses
 *
 */
public class UDPSimpleListener extends UDPServer implements Runnable
{
	private static final Logger LOG = Logger.getLogger(UDPSimpleListener.class.getName());
	
	private static final int MIN_THREADS = 8;
	
	private final ByteBuffer _bufferLeitura;
	private final ByteBuffer _bufferEscrita;

	private final Thread _thread;

	private DatagramSocket _ds;
	
	public UDPSimpleListener()
	{
		super(new PacketHandler(UDPSimpleListener.MIN_THREADS));
		
		_thread = new Thread(this, "UDP Server Thread - Simple I/O");
		_thread.setPriority(Thread.MAX_PRIORITY);
		
		// Buffers precisam ser via wrap() para poder ter acesso ao buffer.array()
		_bufferLeitura = ByteBuffer.wrap(new byte[8192]);
		_bufferEscrita = ByteBuffer.wrap(new byte[8192]);
	}
	
	@Override
	public void start()
	{
		this.setStatus(ServerStatus.STARTING);
		LOG.info("Starting UDPSimpleServer");
		_thread.start();
	}
	
	public void run()
	{
		try
		{
			_ds = new DatagramSocket(9999);
		}
		catch (SocketException e)
		{
			LOG.log(Level.SEVERE, "Falha inicializando socket: "+e.getMessage(), e);
			System.exit(2);
		}
		
		this.setStatus(ServerStatus.RUNNING);
		
		InetSocketAddress origem = null;
		for (;;)
		{
			DatagramPacket packet = new DatagramPacket(_bufferLeitura.array(), _bufferLeitura.capacity()); 
			try
			{
				// reinicia o buffer
				_bufferLeitura.clear();
				
				_ds.receive(packet);
				
				// se o pacote cabe no buffer de leitura
				if (packet.getLength() <= _bufferLeitura.capacity())
				{
					// limita o buffer ao conteudo recebido
					_bufferLeitura.limit(packet.getLength());
					
					
					if (packet.getSocketAddress() instanceof InetSocketAddress)
					{
						origem = (InetSocketAddress) packet.getSocketAddress();
						this.getPacketHandler().processaDados(origem, _bufferLeitura);
					}
					else
					{
						// Nunca deve acontecer, ja que o socket foi criado em IP
						// Exceto numa mudança inconsistente de codigo
						LOG.severe("Um pacote de um protocolo desconhecido(não IP) foi recebido: "+packet.getSocketAddress().getClass().getName());
					}
				}
				else
				{
					// Descartar um pacote que seja maior que o buffer de leitura
					LOG.severe("Descartando pacote grande ("+packet.getLength()+" bytes).");
				}
			}
			catch (IOException e)
			{
				LOG.log(Level.SEVERE, "Erro tentando receber pacote UDP", e);
			}
		}
	}

	@Override
	public void envia(ServerMsg msg)
	{
		// envia no current thread
		synchronized (this)
		{
			_bufferEscrita.clear();
			
			if (msg.writeTo(_bufferEscrita))
			{
				_bufferEscrita.flip();
				
				
				DatagramPacket packet;
				try
				{
					packet = new DatagramPacket(_bufferEscrita.array(), _bufferEscrita.limit(), msg.getSocketAddress());
					
					_ds.send(packet);
				}
				catch (SocketException e)
				{
					LOG.log(Level.SEVERE, "Falha criando pacote. Motivo: "+e.getMessage(), e);
				}
				catch (IOException e)
				{
					LOG.log(Level.SEVERE, "Falha enviando pacote. Motivo: "+e.getMessage(), e);
				}
			}
		}
	}
}
