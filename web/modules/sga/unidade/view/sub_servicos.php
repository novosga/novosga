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
 * Exibe subserviços.
 */
try {
	
//	if(empty($_POST["id_servico"]) ){
//		throw new Exception("serviço não especificado");
//	}
//	$id_macro = $_POST["id_servico"];
	$id_uni = SGA::getContext()->getUser()->get_unidade()->getId();
	$subServicos = DB::getAdapter()->getServicos_sub_nao_cadastrados_uni($id_uni);
	
	$id_macro = "";
	
	$tmp = array();
	foreach ($subServicos as $serv){
		if ($id_macro != $serv[1]){
			$id_macro = $serv[1];
			$tmp["id_macro".$serv[1]] = "-- ".mb_strtoupper(DB::getAdapter()->get_servico($serv[1])->getNome(),'UTF-8')." --";
		}
		$tmp[$serv[0]] = $serv[3];		
	}
	TAdmin::exibir_servicos($tmp,"Subserviços :");
}
catch (Exception $e) {
	Template::display_exception($e);
}
?>