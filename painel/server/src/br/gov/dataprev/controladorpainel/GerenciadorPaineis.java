
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

package br.gov.dataprev.controladorpainel;

import java.net.Inet4Address;
import java.net.InetAddress;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.CopyOnWriteArrayList;
import java.util.logging.Level;
import java.util.logging.Logger;

import br.gov.dataprev.controladorpainel.enviados.SenhaMsg;

/**
 * Gerencia os paineis cadastrados, mantendo eles em memória (performance).
 * Também é responsavel por manter o a memória sincronizada com o banco, ou seja, 
 * garantir que um painel adicionado/atualizado/removido seja adicionado/atualizado/removido no banco.
 * 
 * @author Ulysses Rangel Ribeiro (Dataprev - URES)
 *
 */
public class GerenciadorPaineis
{
	private static final Logger LOG = Logger.getLogger(GerenciadorPaineis.class.getName());
	
	private static final GerenciadorPaineis INSTANCE = new GerenciadorPaineis();
	
	private final ConcurrentHashMap<Integer, CopyOnWriteArrayList<Painel>> _paineisAps = new ConcurrentHashMap<Integer, CopyOnWriteArrayList<Painel>>();
	private final ConcurrentHashMap<InetAddress, Painel> _paineisHost = new ConcurrentHashMap<InetAddress, Painel>();
	
	public static GerenciadorPaineis getInstance()
	{
		return INSTANCE;
	}
	
	private GerenciadorPaineis()
	{
		LOG.info("Carregando paineis a partir do banco");
		this.carregarPaineisDoBanco();
	}
	
	private void carregarPaineisDoBanco()
	{
		Connection con = null;
		try
		{
			con = SQLConnectionPool.getInstance().getConnection();
			PreparedStatement ps = con.prepareStatement("SELECT * FROM paineis");
			ResultSet rset = ps.executeQuery();
			
			PreparedStatement psServ = con.prepareStatement("SELECT * FROM paineis_servicos WHERE host = ?");
			
			int idUnidade;
			InetAddress host;
			int intHost;
			List<Byte> tmpServ = new ArrayList<Byte>();
			
			while (rset.next())
			{
				try
				{
					idUnidade = rset.getInt("id_uni");
					
					intHost = rset.getInt("host");
					host = Inet4Address.getByName(String.valueOf(intHost));
					
					psServ.setInt(1, intHost);
					ResultSet rsetServ = psServ.executeQuery();
					tmpServ.clear();
					while (rsetServ.next())
					{
						tmpServ.add(rsetServ.getByte("id_serv"));
					}
					
					byte[] servicos = new byte[tmpServ.size()];
					for (int i = 0; i < servicos.length; i++)
					{
						servicos[i] = tmpServ.get(i);
					}
					
					this.cadastrarPainel(idUnidade, host, servicos, true);
				}
				catch (Throwable t)
				{
					LOG.log(Level.SEVERE, "Falha carregando painel do banco", t);
				}
			}
			LOG.info(_paineisHost.size()+" painei(s) carregado(s).");
		}
		catch (SQLException e)
		{
			LOG.log(Level.SEVERE, "Falha carregando todos paineis salvos no banco. Motivo: "+e.getMessage(), e);
		}
		finally
		{
			try
			{
				con.close();
			}
			catch (Exception e)
			{
				// nada
			}
		}
	}

	public Painel cadastrarPainel(int apsId, InetAddress hostRemoto, byte[] servicos)
	{
		return this.cadastrarPainel(apsId, hostRemoto, servicos, false);
	}
	
	private Painel cadastrarPainel(int idUnidade, InetAddress hostRemoto, byte[] servicos, boolean naoSalvar)
	{
		synchronized (_paineisAps)
		{
			Painel p = _paineisHost.get(hostRemoto);
			
			// se painel ja existe
			if (p != null)
			{
				// Se o ID da unidade mudou
				if (p.getApsId() != idUnidade) {
					// remove o painel da Unidade Anterior
					this.removerPainel(p);
					
					p.setApsId(idUnidade);
					
					// insere na nova unidade
					this.inserirPainel(p);
					
					LOG.info("Painel atualizado: "+p);
				}
				p.setServicos(servicos);
			}
			else
			{
				p = new Painel(idUnidade, hostRemoto, servicos, naoSalvar);
				this.inserirPainel(p);
				LOG.info("Painel cadastrado: "+p);
			}
			
			
			if (!naoSalvar)
			{
				p.salvar();
				p.marcaContatoAgora();
				p.enviarMsgConfirmacao();
			}
			
			return p;
		}
	}
	
	private void inserirPainel(Painel p)
	{
		synchronized (_paineisAps)
		{
			CopyOnWriteArrayList<Painel> list = _paineisAps.get(p.getApsId());
			if (list == null)
			{
				list = new CopyOnWriteArrayList<Painel>();
				_paineisAps.put(p.getApsId(), list);
			}
			_paineisHost.put(p.getSocketAddress().getAddress(), p);
			
			list.add(p);
		}
	}
	
	public void removerPainel(Painel p)
	{
		synchronized (_paineisAps)
		{
			_paineisHost.remove(p.getSocketAddress().getAddress());
			CopyOnWriteArrayList<Painel> list = _paineisAps.get(p.getApsId());
			if (list != null)
			{
				list.remove(p);
			}
		}
	}
	
	public Collection<Painel> getPaineis()
	{
		return _paineisHost.values();
	}
	
	public Iterable<Painel> getPaineisPorAps(int apsId)
	{
		return _paineisAps.get(apsId);
	}

	public Painel getPainelPorHost(InetAddress host)
	{
		return _paineisHost.get(host);
	}
	
	/**
	 * @param idUnidade
	 * @param msgEspecial
	 * @param codServ
	 * @param senha
	 * @param guicheStr
	 * @param guiche
	 */
	public void despacharSenha(int idUnidade, String msgEspecial, int id_serv, char sig_serv, int senha, String guicheStr, int guiche)
	{
		Iterable<Painel> paineis = this.getPaineisPorAps(idUnidade);
		//System.err.println("IDUNIDADE: "+idUnidade+" SENHA: "+sig_serv+senha+" ITERABLE: "+paineis);
		if (paineis != null)
		{
			for (Painel p : paineis)
			{
				//LOG.info("Painel: "+p+" - Exibe Serviço da senha: "+p.seInteressaPorServico(id_serv)+" - Esta ativo: "+!p.expirou()+" - Expira em: "+p.segundosExpirados()+" segs");
				if (p.seInteressaPorServico(id_serv) && !p.expirou())
				{
					SenhaMsg sm = new SenhaMsg(p, msgEspecial, sig_serv, senha, guicheStr, guiche);
					sm.envia();
				}
			}
		}
	}
}
