
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

import java.util.ArrayList;

/**
 * @author DATAPREV
 * @version 1.0
 * @category Interface
 */
public class VetServCheck
{
	private ArrayList<ServCheck> list = new ArrayList<ServCheck>();
	
	/**
	 * retorna lista de IDs de servi�o
	 * 
	 * @return
	 */
	public String toStringArquivo()
	{
		StringBuilder sb = new StringBuilder();
		for (int i = 0; i < this.list.size(); i++)
		{
			ServCheck servCheck = this.list.get(i);
			if (servCheck.getCheck().isSelected())
			{
				sb.append(servCheck.getId());
				sb.append(',');
			}
		}
		return sb.toString();
	}
	
	/**
	 * lista de ServCheck
	 * 
	 * @return
	 */
	public ArrayList<ServCheck> getList()
	{
		return list;
	}
	
	public int size()
	{
		return this.list.size();
	}
	
	public void clear()
	{
		this.list.clear();
	}
	
	/**
	 * insere ServCheck na lista
	 * 
	 * @param serv
	 * @throws Exception
	 */
	public void inserir(ServCheck serv)
	{
		this.list.add(serv);
	}
	
	/**
	 * retorna um ServCheck
	 * 
	 * @param i
	 * @return
	 */
	public ServCheck getServCheck(int i)
	{
		return this.list.get(i);
	}
	
	public void setCheck(int i, boolean marca)
	{
		this.list.get(i).setSelect(marca);
	}
	
	/**
	 * retorna lista de IDs de servi�os
	 * 
	 * @return
	 */
	public int[] getServ()
	{
		ArrayList<Integer> list = new ArrayList<Integer>();
		for (int i = 1; i < this.list.size(); i++)
		{
			if (this.list.get(i).getCheck().isSelected())
			{
				list.add(this.list.get(i).getId());
			}
		}
		
		int[] ret = new int[list.size()];
		for (int i = 0; i < list.size(); i++)
		{
			ret[i] = list.get(i);
		}
		
		return ret;
	}
}
