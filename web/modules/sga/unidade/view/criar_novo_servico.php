<?php

/**
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro dos termos da Licença Pública Geral GNU como 
 * publicada pela Fundação do Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA; sem uma garantia implícita
 * de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt", junto com este programa, se
 * não, escreva para a 
 * Fundação do Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 */

SGA::check_login('sga.admin');
/**
 * Confere e salva dados do novo serviço
 */
try {
	$id_serv = $_POST["novo_servico"];
	if(empty($id_serv) ){
		throw new Exception("Serviço não especificado.");
	}
	$sigla = $_POST["id_text_sigla"];
	if(empty($sigla) ){
		throw new Exception("Sigla não especificada.");
	}
	
	$id_loc= 1;
	$nome_novo_serv = $_POST["id_text_novo"];
	if(empty($nome_novo_serv) ){
		throw new Exception("Nome do serviço não especificado.");
	}
	$status_serv = $_POST["status_serv"];
	if(empty($status_serv) ){
		throw new Exception("Status não especificado.");
	}
	$status_serv = ($status_serv=='true')? 1:0;

	$criar = $_POST["criar"];
	$id_uni = SGA::getContext()->getUser()->get_unidade()->getId();
	if ($criar != "false") {
		//criando
		$servicos_uni = DB::getAdapter()->getServicos_unidade($id_uni);
		$verificacao = true;
	
		foreach ($servicos_uni as $serv_uni) {
			if ($serv_uni->getNome() == $nome_novo_serv) {
				$verificacao = false;
				TAdmin::display_error("O nome do serviço já existe.","Serviço");
                break;
			}
		}

		if ($verificacao) {
			DB::getAdapter()->inserir_servico_uni($id_uni,$id_serv,$id_loc,$nome_novo_serv,$sigla,$status_serv);
		}
	}
    else {
    	if(DB::getAdapter()->get_stat_serv($id_serv) != 0 || $status_serv == 0 ){
    	
			//editando
			$servicos_uni = DB::getAdapter()->getServicos_unidade($id_uni);
			$verificacao = true;
			foreach ($servicos_uni as $serv_uni){
				if ($id_serv != $serv_uni->getId()){	
					if ($serv_uni->getNome() == $nome_novo_serv){
						$verificacao = false;
						TAdmin::display_error("O nome do serviço já existe.","Serviço");
	                    break;
					}
				}
			}
	
			if ($verificacao) {
				DB::getAdapter()->alterar_servico_uni($id_uni,$id_serv,$id_loc,$nome_novo_serv,$sigla,$status_serv);
			}
    	}else{
    		throw new Exception("Macrosserviço está desativado. Não é possível ativar.");
    	}
	}

	// se houve sucesso responder com true para o javascript
    if ($verificacao) {
        echo "true";
    }
}
catch (PDOException $e) {
	if ($e->getCode() >= 23000 && $e->getCode() <= 23999) {
		Template::display_error('O serviço já está presente na unidade.');
	}
	else {
		Template::display_exception($e);
	}
}
catch (Exception $e) {
	Template::display_exception($e);
}
