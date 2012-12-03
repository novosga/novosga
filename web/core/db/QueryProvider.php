<?php

/**
 * Interface do Banco de Dados do Sistema
 * 
 * Deve ser implementada com metodos para utilizar
 * o banco de dados desejado.
 * 
 * Todos os parametros das Queries serao informados internamente
 * (conforme a classe exemplo PgSQLQueries) para serem "bindados"
 * pelo PDO.
 *
 */
interface QueryProvider {
    
    public function salvarSessionId();
    
    public function chamaProximo();
    
    /*
     * getting - prioridades
     */
    
    public function selectPrioridade();
    
    public function selectPrioridades();
    
    public function selectPrioridadesByNomeOrDescricao();
    
    /*
     * getting - usuarios
     */
    
    public function selectUsuario();
    
    public function selectUsuarios();
    
    public function selectUsuarioByLogin();
    
    public function selectUsuariosByLoginOrNome();
    
    /*
     * getting - modulos
     */
    
    public function selectModulo();
    
    public function selectModuloByChave();

    public function selectModulos();
    
    public function selectUnidade();
    
    /*
     * getting - grupos
     */
    
    public function selectGrupo();
    
    public function selectGrupos();
    
    public function selectGruposByNomeOrDescricao();

    public function selectGruposPaiByFilho();
    
    /*
     * getting - cargos
     */
    
    public function selectCargo();
    
    public function selectCargos();
    
    public function selectCargosByNomeOrDescricao();
    
    public function selectCargosPaiByFilho();
    
    /*
     * getting - unidades
     */
    
    public function selectUnidades();
    
    public function selectUnidadesByCodigoOrNome();
    
    public function selectUnidadesByUsuario();
    
    /*
     * getting - menu
     */
    
    public function selectMenuByModulo();
    
    /*
     * getting - servicos
     */
    
    public function selectServico();
    
    public function selectServicos();
                    
    public function selectServicosByNomeOrDescricao();
    
    public function selectServicosMestre();
    
    public function selectServicosUnidade();
    
    /*
     * getting - atendimentos
     */
    
    public function selectAtendimentosByServicoUnidade();
    
    /*
     * inserting
     */

    public function insertGrupo();
    
    public function insertCargo();
    
    public function insertUnidade();
    
    public function insertPrioridade();
    
    public function insertUsuario();

    public function insertServico();

    public function insertServicoUnidade();

    public function insertLotacao();

    public function insertPermissaoModulo();
    
    /*
     * updating
     */

    public function updateGrupo();
    
    public function updateCargo();
    
    public function updateUnidade();
    
    public function updatePrioridade();
    
    public function updateUsuario();

    public function updateServico();
    
    public function updateSubServicos();

    public function updateServicoUnidade();

    public function updateLotacao();

    public function updateStatusSubServicos();
    
    /*
     * util - triagem
     */
    
    public function distribuirSenha();

}
