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
 * Remove um serviço
 */
try {
	$id_serv = $_POST["id_serv"];
	if(empty($id_serv) ){
		throw new Exception("Serviço não especificado.");
	}
	$id_uni = SGA::getContext()->getUser()->get_unidade()->getId();
//	var_dump($id_uni,$id_serv,$id_loc,$nome_novo_serv,$sigla);
	DB::getAdapter()->remover_servico_uni($id_uni,$id_serv);
	TAdmin::display_confirm_dialog("Serviço excluído com sucesso.","Excluir Serviço");
	}catch (PDOException $e) {
	if($e->getCode() == 23503){
		Template::display_error('O serviço não pode ser removido pois há atendimentos associados a ele.');
	}
	else {
		Template::display_exception($e);
	}
}
catch (Exception $e) {
	Template::display_exception($e);
}
?>