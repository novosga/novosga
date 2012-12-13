
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

public class Data {
	private int dia;
	private int mes;
	private int ano;

	public Data(){
		this.padrao();
	}
	public Data(int dia,int mes,int ano){
		this.setData(dia+"/"+mes+"/"+ano);
	}
	public Data(String data){
		this.setData(data);
	}
	public String toString(){
		String msg = "";
		if(this.getDia()<10)
			msg += "0";
		msg += this.getDia()+"/";
		if(this.getMes()<10)
			msg += "0";
		msg += this.getMes()+"/";
		msg += this.getAno();
		return msg;
	}
	public void setData(String data){
		try{
			int aux1,aux2,d,m,a;
			aux1 = data.indexOf("/");
			d = Integer.parseInt(data.substring(0,aux1));

			aux1++;
			aux2 = data.indexOf("/",aux1);

			m = Integer.parseInt(data.substring(aux1, aux2));
			a = Integer.parseInt(data.substring(++aux2));

			if(this.validaData(d, m, a)){
				this.setDia(d);
				this.setMes(m);
				this.setAno(a);
			}else
				throw new Exception("Data inv�lida");
		}catch(Exception e){
			this.padrao();
		}
	}
	public int compareTo(Data dt2){
		Data dt1 = new Data(this.dia,this.mes,this.ano);
		if(dt1.getAno()>dt2.getAno())
			return 1;
		else if(dt1.getAno()<dt2.getAno())
			return -1;
		if(dt1.getMes()>dt2.getMes())
			return 1;
		else if(dt1.getMes()<dt2.getMes())
			return -1;
		if(dt1.getDia()>dt2.getDia())
			return 1;
		else if(dt1.getDia()<dt2.getDia())
			return -1;
		return 0;
	}
	public static int compareTo(Data dt1,Data dt2){
		if(dt1.getAno()>dt2.getAno())
			return 1;
		else if(dt1.getAno()<dt2.getAno())
			return -1;
		if(dt1.getMes()>dt2.getMes())
			return 1;
		else if(dt1.getMes()<dt2.getMes())
			return -1;
		if(dt1.getDia()>dt2.getDia())
			return 1;
		else if(dt1.getDia()<dt2.getDia())
			return -1;
		return 0;
	}
	public void adicionaDia() throws Exception{
		int dia,mes,ano;
		dia = this.getDia();
		mes = this.getMes();
		ano = this.getAno();
		dia++;
		try{
			this.setData(dia+"/"+mes+"/"+ano);
		}catch(Exception erro1){
			dia = 1;
			mes++;
			try{
				this.setData(dia+"/"+mes+"/"+ano);
			}catch(Exception erro2){
				dia = 1;
				mes = 1;
				ano++;
				try{
					this.setData(dia+"/"+mes+"/"+ano);
				}catch(Exception erro){
					throw new Exception("Data inv�lida");
				}
			}
		}
	}
	public void adicionaDia(int dias) throws Exception{
		while(dias-->0){
			this.adicionaDia();
		}
	}
	public boolean validaData(int dia,int mes, int ano){
		switch(mes){
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				if(dia>0 && dia <=31)
					return true;
				return false;
			case 4:
			case 6:
			case 9:
			case 11:
				if(dia>0 && dia <=30)
					return true;
				return false;
			case 2:
				if(this.isBissexto(ano)){
					if(dia>0 && dia <=29)
						return true;
					return false;
				}
				if(dia>0 && dia <=28)
					return true;
				return false;
			default:
				return false;
		}
	}
	private boolean isBissexto(int ano){
		if(ano%400==0)
			return true;
		else if(ano%100==0 && ano%400!=0){
			return false;
		}else if(ano%4==0 && ano%100!=0)
			return true;
		return false;
	}

	private void padrao(){
		this.setDia(1);
		this.setMes(1);
		this.setAno(1900);
	}
	private void setDia(int dia) {
		this.dia = dia;
	}

	private void setMes(int mes) {
		this.mes = mes;
	}

	private void setAno(int ano) {
		this.ano = ano;
	}

	public int getDia() {
		return dia;
	}

	public int getMes() {
		return mes;
	}

	public int getAno() {
		return ano;
	}

}
