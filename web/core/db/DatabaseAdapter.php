<?php

/**
 * Database Adapter
 *
 * Responsavel pela abstracao do Banco de Dados
 *
 */
interface DatabaseAdapter {
    
    public function connect($host, $port, $user, $pass, $dbname);
    
    public function lastInsertId($table = '', $id = '');
    
    public function begin();
    
    public function commit();
    
    public function rollback();
    
    public function inTransaction();
    
    /**
     * @return Connection
     */
    public function getConnection();
    
    /*
     * auth
     */

    public function hasAcesso(Usuario $usuario, $chave_mod);
    
    public function hasAccessGlobal($id_usu, $id_mod);

    public function setSessionStatus($id_usu, $stat_session);
    
    public function salvarSessionId($id_usu, $session_id = null);

    public function verificarSessionId($id_usu, $session_id = null);
    
    /*
     * getting - modulos
     */
    
    public function getModulo($id);

    public function getModulos($status = array(Modulo::MODULO_ATIVO, Modulo::MODULO_INATIVO), $tipos = array(Modulo::MODULO_GLOBAL, Modulo::MODULO_UNIDADE));
    
    public function getModulosUnidade();
    
    public function getModulosGlobal();
    
    public function getModuloByChave($chave, $status = Modulo::MODULO_ATIVO);

    /*
     * getting - prioridades
     */
    
    public function getPrioridade($id);
    
    public function getPrioridades();
    
    public function getPrioridadesByNomeOrDescricao($arg);

    /*
     * getting - usuarios
     */
    
    public function getUsuario($id);
    
    public function getUsuarios();

    public function getUsuarioByLogin($login);
    
    public function getUsuariosByLoginOrNome($arg);
    
    /*
     * getting - grupos
     */
    
    public function getGrupo($id);
    
    public function getGrupos();
    
    public function getGruposByNomeOrDescricao($arg);

    public function getGrupoPaiByFilho(Grupo $filho);
    
    public function getGruposPaiByFilho(Grupo $filho);
    
    /*
     * getting - cargos
     */
    
    public function getCargo($id);
    
    public function getCargos();
    
    public function getCargosByNomeOrDescricao($arg);
    
    public function getCargoPaiByFilho(Cargo $cargo);
    
    public function getCargosPaiByFilho(Cargo $cargo);
    
    /*
     * getting - unidades
     */
    
    public function getUnidade($id);
    
    public function getUnidades();
    
    public function getUnidadesByCodigoOrNome($arg);
    
    public function getUnidadesByUsuario(Usuario $usuario);
    
    
    /*
     * getting - servicos
     */
    
    public function getServico($id);
    
    public function getServicos();
    
    public function getServicosByNomeOrDescricao($arg);
    
    public function getServicosMestre();
    
    public function getServicosUnidade(Unidade $unidade);
    
    /*
     * getting - atendimentos
     */
    
    public function getAtendimentosByServicoUnidade(ServicoUnidade $servico);
    
    /*
     * inserting
     */
    
    public function insertUsuario(Usuario $usuario, $senha);
    
    public function insertServico(Servico $servico);
    
    public function insertPrioridade(Prioridade $prioridade);
    
    /*
     * updating
     */
    
    public function updateGrupo(Grupo $grupo);
    
    public function updateCargo(Cargo $cargo);
    
    public function updateUsuario(Usuario $usuario);
    
    public function updateServico(Servico $servico);
    
    public function updateSubServicos(Servico $macro);
    
    public function updatePrioridade(Prioridade $prioridade);
       
}