<?php

/**
 * Database Adapter
 *
 * Responsavel pela abstracao do Banco de Dados
 *
 */
interface _DatabaseAdapter {

    public function getUnidadesByUsuario($id_usuario);

    public function getUnidadesByGrupo($id_grupo);

    public function getUnidadesVisiveis(Usuario $usuario);

    public function getTemas();
    
    public function getModulo($chave, $status = MOdulo::MODULO_ATIVO);

    public function getModulos($status = array(Modulo::MODULO_ATIVO, Modulo::MODULO_INATIVO), $tipos = array(Modulo::MODULO_GLOBAL, Modulo::MODULO_UNIDADE));

    public function isModuloInstalado($chave);
    
    public function getUsuario($id);
    
    public function getUsuarios();

    public function getUsuarioByLogin($login, $status = Usuario::USUARIO_ATIVO);
    
    public function getUsuariosByLoginOrNome($nome, $status = Usuario::USUARIO_ATIVO);
    
    public function getServicosUsuarioUnidade($id_user, $id_uni, $status = array(Servico::SERVICO_ATIVO));
    
    public function getGrupos();

    public function getArvoreGrupos();
    
    public function getGruposCandidatosPai($id_grupo);
    
    public function getGruposFolhaDisponiveis();
    
    public function getGrupos_by_permissao_usuario($id_usu, $id_mod);
    
    public function getGrupo($id);
    
    public function getSubGrupos($id_pai);
    
    public function getGrupoPaiById($id_filho);
    
    public function getCargo($id);
    
    public function getSubCargos($id);
    
    public function getCargos();

    public function getArvoreCargos();

    public function getCargosCandidatosPai($id);
    
    public function getCargoPaiById($id_filho);
    
    public function getPermissoesCargo($id);
    
    public function getLotacoesEditaveis($id_usu, $id_mod, $id_grupo = null, $filtrar_redundancia = false);

    public function getLotacoesVisiveis($id_usu, $id_usu_admin, $id_mod, $id_grupo);
    
    public function getLotacao($id_usu, $id_grupo);
    
    public function get_cargo_by_usuario_grupo($id_usu, $id_grupo);
    
    public function get_permissao_modulo_grupo($id_usu, $chave_mod, $id_uni);
    
    public function getMenu($id);
    
    public function getMenus(Modulo $modulo);

    public function getTotalFila(Unidade $unidade);

    public function getUltimaSenha(Unidade $unidade, $ids_stat = array(Atendimento::SENHA_EMITIDA));
    
    public function getProximaSenha();

    public function getServico($id);
    
    public function getServicos();
    
    public function getLotacaoValida($id_usu, $id_grupo);
    
    public function getServicosUnidade(Unidade $unidade, $status = array(Servico::SERVICO_ATIVO, Servico::SERVICO_INATIVO));
    
    public function getServicosMestreUnidade(Unidade $unidade, $status = array(Servico::SERVICO_ATIVO, Servico::SERVICO_INATIVO));

    public function getServicosUnidadeErroTriagem(Unidade $unidade, $id_usu, $stats_serv = array(Servico::SERVICO_ATIVO, Servico::SERVICO_INATIVO));

    public function getServicosDisponiveisUnidade(Unidade $unidade, $status = array(Servico::SERVICO_ATIVO, Servico::SERVICO_INATIVO));
    
    public function getServicosUnidadeReativar(Unidade $unidade, $status);
    
    public function getServicosUnidadeTransfereSenha($status, $id_uni, $id_serv);
    
    public function getServicosMestre($stats_serv = array(Servico::SERVICO_ATIVO, Servico::SERVICO_INATIVO));
    
    public function getServicosSubUnidade($mestre, $status, $id_uni);
    
    public function getSubServicos($id_macro, $stats_serv = array(Servico::SERVICO_ATIVO, Servico::SERVICO_INATIVO));
    
    public function getSubServicosNaoUnidade($id_uni);
    
    public function getFila($servicos, $id_uni, $ids_stat = array(Atendimento::SENHA_EMITIDA));
    
    public function getAtendimento($id);
    
    public function getAtendimentosByUsuario(Usuario $usuario, Unidade $unidade, $status);
    
    public function getAtendimentoPorSenha(Senha $senha, Unidade $unidade, $status = Atendimento::SENHA_EMITIDA);
    
    public function getPrioridades();
    
    public function getTotalFilaServico(Unidade $unidade, Servico $servico);
    
    public function getTotalFilaUsuario(Unidade $unidade, Usuario $usuario);

    public function getAtendimentoSenhaPeriodo($num_senha, $id_uni, $data_inicio, $data_fim);
    
    public function getQuantidadeSenhasPorStatus($ids_uni, $dt_min, $dt_max);
    
    public function getTemposAtendimentoPorUsuario($ids_uni, $dt_min, $dt_max);

    public function getTemposMediosByPeriodo($ids_uni, $dt_min, $dt_max);
    
    public function getMacroServicosNaoUnidade($id_uni);

    public function getRankingUnidades($ids_uni, $dt_min, $dt_max);
    
    public function getEstatisticaTemposMedios($ids_uni, $dt_min, $dt_max);
    
    public function getEstatisticaServicosMestres($ids_uni, $dt_min, $dt_max);
    
    public function getEstatisticaServicosCodificados($ids_uni, $dt_min, $dt_max);
    
    public function getEstatisticaAtendimentosEncerrados($ids_uni, $dt_min, $dt_max);

    public function getEstatisticaMacroServicoGlobal($ids_uni, $dt_min, $dt_max);

    public function getEstatisticaAtendimentosGlobal($ids_uni, $dt_min, $dt_max);
    
    public function getEstatisticaAtendimentosUsuario($ids_uni, $dt_min, $dt_max);

    public function getEstatisticaAtendimentos($ids_uni, $dt_min, $dt_max);
    
    /*
     * inserting
     */
    
    public function insertServicoUsuario(Unidade $unidade, Servico $servico, Usuario $usuario);
    
    public function insertUnidade(Unidade $unidade);
    
    public function insertCargo(Cargo $cargo);
    
    public function insertGrupo(Grupo $grupo);
    
    public function insertUsuario(Usuario $user, $pass);
    
    public function insertPermissaoModuloCargo(Cargo $cargo, Modulo $modulo, $permissao);
    
    public function insertLotacao(Lotacao $lotacao);
    
    public function insertServico(Servico $servico);
    
    public function insertServicoUnidade(Unidade $unidade, Servico $servico);
    
    /*
     * updating
     */
    
    public function updateMensagemUnidade(Usuario $usuario, $mensagem);
    
    public function updateMensagemGlobal(Usuario $usuario, $mensagem);
    
    public function updateUsuario(Usuario $usuario);
   
    public function updateCargo(Cargo $cargo);
    
    public function updateGrupo(Grupo $grupo);

    public function updateServico(Servico $servico);
    
    public function updateLotacao(Lotacao $lotacao);
    
    public function updateUnidade(Unidade $unidade);
    
    public function updateSenhaUsuario($id, $novaSenha, $senhaAtual = null);
    
    public function updateServicoUnidade(Servico $servico);
    
    /*
     * removing
     */
    
    public function removeUsuario($id);
    
    public function removeCargo($id);
    
    public function removeGrupo($id);
    
    public function removeUnidade($id);
    
    public function removeServico($id);
    
    public function removePermissaoModuloCargo($id_cargo, $id_mod);
    
    public function removeLotacao($id_usuario, $id_grupo);

    public function removeLotacoes($id_usuario);
    
    /*
     * system utils
     */

    public function updatePainel();
    
    public function chamarProximo(Unidade $unidade, Servico $servico, Senha $senha);
    
    public function chamarProximoAtendimento($id_usu, $id_uni, $servicos, $num_guiche);
    
    public function transferirSenha(Atendimento $atendimento, Servico $servico, Prioridade $prioridade);
    
    public function distribuirSenha(Unidade $unidade, Servico $servico, Senha $senha);
    
    public function erroTriagem($id_uni, $id_serv, $num_senha, $id_pri, $num_guiche, $id_stat = 1, $nm_cli = "", $ident_cli="", $dt_cheg = "");
    
    public function encerrarAtendimentos($id_atend, $id_uni, $array);
    
    public function encerrarAtendimento($id_atend, $id_uni, $id_serv, $valor_peso);
    
    public function reiniciarSenhasUnidade(Unidade $unidade, $dataMaxima = null);

    public function reiniciarSenhasGlobal($dataMaxima = null);
    
}