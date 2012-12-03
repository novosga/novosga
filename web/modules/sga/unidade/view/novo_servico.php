<?php

/**
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
 */

SGA::check_login('sga.admin');
/**
 * Exibe a janela para a criação de um novo serviço
 */
try {
	$id_uni = SGA::getContext()->getUser()->get_unidade()->getId();
	
	$tmp = array();	
	
	$id_serv = isset($_POST["id_serv"]) ? $_POST["id_serv"] : '';
	$nome_serv= '';
	$status_serv = 0;
	$sigla_serv= '';
	if(empty($id_serv) ){
		$servicos = DB::getAdapter()->getServicos_macro_nao_cadastrados_uni($id_uni);
		foreach ($servicos as $s) {
			$tmp[$s[0]] = $s[3]; 
		}	
	}else{
		$nome_serv= $_POST["nome_serv"];
		if(empty($nome_serv) ){
			throw new Exception("Nome não especificado.");
		}
		$sigla_serv= $_POST["sigla_serv"];
		if(empty($sigla_serv) ){
			throw new Exception("Sigla não especificada.");
		}
		$status_serv = $_POST["status_serv"];
		if(empty($status_serv) ){
			$status_serv= 0;
		}
		$tmp[$id_serv] = $nome_serv;
		
	}
	Template::display_popup_header((empty($id_serv)? "Novo Serviço" : "Alterar Serviço"));
	TAdmin::exibir_novo_servico($tmp,$id_serv,$nome_serv,$sigla_serv,$status_serv);
	Template::display_popup_footer();	
	
}
catch (Exception $e) {
	Template::display_exception($e);
}
?>
