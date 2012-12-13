
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

import java.util.Date;

public class VetSenhas {
	private Senha[] lista;
	private int indice;
	private int ultimo = 0;
	private int dataExibicao;

	@SuppressWarnings("deprecation")
	public VetSenhas(int tam){
		this.lista = new Senha[tam];
		this.indice = 0;
		this.dataExibicao = new Date().getDate();
	}
	public int pesquisaSenha(int id){
		for(int i=0;i<this.indice;i++)
			if(this.lista[i].getId()==id)
				return i;
		return -1;
	}
	public int proximoFila(){
		for(int i=0;i<this.indice;i++)
			if(!this.lista[i].isStatus())
				return this.lista[i].getId();
		return -1;
	}
	@SuppressWarnings("deprecation")
	public void insereUltimo(Senha senha) throws Exception{
		if(this.indice>=this.lista.length)
			while(this.lista[0].isStatus()){
				System.out.println("OI");
				removePrimeiro();
			}
		try{
			if(new Date().getDate() != this.dataExibicao){
				this.dataExibicao = new Date().getDate();
				this.ultimo = 0;
			}
			if(this.pesquisaSenha(senha.getId())<0 && senha.getId()>this.ultimo){
				this.lista[this.indice] = senha;
				this.indice++;
				this.ultimo = senha.getId();
			}
		}catch(Exception err){}
	}
	private void removePrimeiro(){
		for(int i=0;i<this.indice-1;i++)
			this.lista[i] = this.lista[i+1];
		this.indice--;
	}
	public int ultimoChamado(){
		for(int i=0;i<this.indice;i++)
			if(!this.lista[i].isStatus())
				return (i-1);
		return this.indice-1;
	}
	public Senha getSenha(int i){
		return this.lista[i];
	}
	public void setSenha(int i,Senha senha){
		this.lista[i] = senha;
	}
	public Senha[] getLista() {
		return lista;
	}

	public void setLista(Senha[] lista) {
		this.lista = lista;
	}

	public int getIndice() {
		return indice;
	}

	public void setIndice(int indice) {
		this.indice = indice;
	}
}
