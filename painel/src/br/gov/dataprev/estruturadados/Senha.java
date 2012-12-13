
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

public class Senha {
	private int id;
	private String msgEspecial;
	private String senha;
	private String guiche;
	private String numGuiche;
	private boolean status;

	public Senha(int id,String msg_especial,String senha,String guiche,String num_guiche){
		this.setId(id);
		this.setMsgEspecial(msg_especial);
		this.setSenha(senha);
		this.setGuiche(guiche);
		this.setNumGuiche(num_guiche);
		this.setStatus(false);
	}

	public boolean isStatus() {
		return status;
	}

	public void setStatus(boolean status) {
		this.status = status;
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getMsgEspecial() {
		return msgEspecial;
	}

	public void setMsgEspecial(String msgEspecial) {
		this.msgEspecial = msgEspecial;
	}

	public String getSenha() {
		return senha;
	}

	public void setSenha(String senha) {
		this.senha = senha;
	}

	public String getGuiche() {
		return guiche;
	}

	public void setGuiche(String guiche) {
		this.guiche = guiche;
	}

	public String getNumGuiche() {
		return numGuiche;
	}

	public void setNumGuiche(String numGuiche) {
		this.numGuiche = numGuiche;
	}
}
