
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

import java.awt.Font;

import javax.swing.JCheckBox;
import javax.swing.JLabel;
/**
 * @author DATAPREV
 * @version 1.0
 * @category Interface
 */
public class ServCheck {
	private JCheckBox check;
	private int id;
	private JLabel descricao;
	private JLabel sigla;
	/**
	 * construtor
	 * @param id
	 * @param descricao
	 * @param sigla
	 * @param ck
	 */
	public ServCheck(int id,String descricao,String sigla,JCheckBox ck){
		this.setId(id);
		this.setCheck(ck);
		this.setSigla(sigla);
		this.setDescricao(descricao);
	}
	/**
	 * retorna JCheckBox
	 * @return
	 */
	public JCheckBox getCheck() {
		return check;
	}
	/**
	 * 
	 * @param check
	 */
	public void setCheck(JCheckBox check) {
		this.check = check;
	}
	/**
	 * retorna ID de servi�o
	 * @return
	 */
	public int getId() {
		return id;
	}
	/**
	 * seta ID de servi�o
	 * @param id
	 */
	public void setId(int id) {
		this.id = id;
	}
	/**
	 * retorna Descri��o do servi�o
	 * @return
	 */
	public JLabel getDescricao() {
		return descricao;
	}
	/**
	 * seta Descri��o de servi�o
	 * @param descricao
	 */
	public void setDescricao(String descricao) {
		this.descricao = new JLabel();
		this.descricao.setText(descricao);
		this.descricao.setFont(new Font("",Font.PLAIN,11));
		this.descricao.setBounds(this.getCheck().getX()+35,this.getCheck().getY(),260,this.getCheck().getHeight());
	}
	/**
	 * retorna sigla de servi�o
	 * @return
	 */
	public JLabel getSigla() {
		return sigla;
	}
	/**
	 * seta sigla de servi�o
	 * @param sigla
	 */
	public void setSigla(String sigla) {
		this.sigla = new JLabel();
		this.sigla.setText(sigla);
		this.sigla.setFont(new Font("",Font.BOLD,11));
		this.sigla.setBounds(this.getCheck().getX()+20,this.getCheck().getY(),20,this.getCheck().getHeight());
	}
	/**
	 * verifica se o servi�o est� selecionado
	 * @param marca
	 */
	public void setSelect(boolean marca){
		this.check.setSelected(marca);
	}
}
