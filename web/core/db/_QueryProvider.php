<?php

interface _QueryProvider {

    /**
     * Retorna a Query para se obter todas as unidades que pertencem a um grupo ou subgrupo ao qual
     * o usuario está associado.
     * 
     * @return String (SQL Query)
     */
    public function get_unidades_by_usuario();

    /**
     * Retorna a Query para se obter todas as unidades que pertencem os grupos(Grupo) especificados
     *
     * @return String (SQL Query)
     */
    public function get_unidades_by_grupos();
    
    /**
     * Retorna a Query para se obter todas as unidades que pertencem os grupos(Grupo) especificados para o modulo Usuario
     *
     * @return String (SQL Query)
     */
    public function get_unidades_by_grupos_mod_usu();
    
    
    /**
     * Retorna a Query para se obter a unidade lotada no grupo(Grupo) especificado, se houver
     *
     * @return String (SQL Query)
     */
    public function get_unidade_by_grupo();

    /**
     * Retorna a Query para pegar todos os Temas disponiveis
     * 
     * @return String (SQL Query)
     */
    public function get_temas();

    /**
     * Retorna a Query para pegar os Modulos do sistema especificando
     * o seu status (habilitado ou desabilitado)
     * 
     * @return String (SQL Query)
     */
    public function get_modulos();

    /**
     * Retorna a Query para pegar o Modulo do sistema, especificando sua chave,
     * e o seu status (habilitado ou desabilitado)
     * 
     * @return String (SQL Query)
     */
    public function get_modulo();

    /**
     * Retorna a Query para pegar se o modulo esta ou nao instalado.
     * 
     * @return String (SQL Query)
     */
    public function is_instalado();

    /**
     * Retorna a Query para pegar se usuario tem ou nao acesso ao modulo.
     * Informando a login, Senha e Chave do Modulo.
     *
     * @return String (SQL Query)
     */
    /* DELETAR public function has_acesso();*/
    
    /**
     * Retorna a Query para pegar se usuario tem ou nao acesso ao modulo
     *
     * @return String (SQL Query)
     */
    public function getLotacao_valida();    

    /**
     * Retorna a Query para pegar o Usuario a partir de seu login
     * 
     * @return String (SQL Query)
     */
    public function get_usuario();
    
    /**
     *  Retorna a Query para pegar  Usuario a partir de seu ID
     * @return Usuario
     */
    public function get_usuario_by_id();
    
    /**
     * Retorna a Query para pegar a lista de Usuarios a partir de um nome
     * 
     * @return String (SQL Query)
     */
    public function get_usuarios_by_name();

    /**
     * Retorna um array contendo todos os usuarios que estão 
     * dentro dos grupos e grupos filhos do usuário passado
     * como parâmetro e todos os usuários
     * que não estão lotados em nenhum grupo
     * 
     * @return String (SQL Query)
     * @author robson
     */
    public function get_usuarios_grupos_by_usuario();
    
    /**
     * Retorna a Query para pegar um array contendo todos os usuarios do sistema,
     * de acordo com o status
     * 
     * @return String (SQL Query)
     */
    public function get_usuarios();

    
    /**
     * Retorna a Query para pegar um array contendo todos usuarios do sistema,
     * 
     * @return String (SQL Query)
     */
    public function get_todos_usuarios();
    
    /**
     * Retorna a Query para pegar um array contendo os servicos do usuario
     * de acordo com o login
     * 
     * @return String (SQL Query)
     */
    public function get_usuario_servicos_unidade();
    
    /**
     * Retorna a Query para inserir um novo usuario
     * 
     * @return String (SQL Query)
     */
    public function inserirUsuario();
    
    /**
     * Retorna a Query para atualizar um usuario
     * 
     * @return String (SQL Query)
     */
    public function atualizarUsuario();
    
    /**
     * Retorna a Query para inserir uma nova unidade
     * 
     * @return String (SQL Query)
     */    
    public function inserirUnidade();
    
    /**
     * Retorna a Query para atualizar uma unidade
     * 
     * @return String (SQL Query)
     */
    public function atualizarUnidade(); 
    /**
     * Retorna a Query para pegar um array contento todos os grupos (Grupo) 
     * que o Usuario participa
     * 
     * @return String (SQL Query)
     */
    /* DELETAR public function get_usuario_grupos();*/
    
    /**
     * Retorna a Query para pegar todos os grupos (Grupo) do Sistema
     * 
     * @return String (SQL Query)
     */
    public function getGrupos();
    
    /**
     * Retorna a Query para pegar os filhos imediatos de um determinado grupo
     * 
     * @return String (SQL Query)
     */
    public function get_arvore_grupos();
    
    /**
     * Retorna a Query para pegar todos os grupos (Grupo) candidatos a pai de um determinado grupo
     * 
     * @return String (SQL Query)
     */
    public function getGrupos_candidatos_pai();
    
    /**
     * Retorna a Query para pegar todos os grupos (Grupo) do Sistema que são folha(não possuem grupos como filhos) e que 
     * ainda nao possuem uma unidade alocada
     * 
     * @return string (SQL Query)
     */
    public function getGrupos_folha_disponiveis();
    
    /**
     * Retorna a Query para pegar todos os grupos(Grupo) DIRETOS(sub-grupos desses grupos não são retornados) de um usuario
     * 
     * @return String (SQL Query)
     */
    public function get_lotacoes_editaveis();
    
    /**
     * Retorna a Query para pegar todos os grupos(Grupo) com seus respectivos sub-grupos de um usuario
     * onde este usuario tem permissão de acesso em um determinado módulo.
     * 
     * @return String (SQL Query)
     */
    public function getGrupos_by_permissao_usuario();
    
    
    /**
     * Retorna a Query para pegar um grupo através de seu ID
     * 
     * @return String (SQL Query)
     */
    public function getGrupo_by_id();
    
    /**
     * Retorna a Query para pegar todos os filhos de um grupo
     * 
     * @return String (SQL Query)
     */
    public function get_sub_grupos();
    
    /**
     * Retorna a Query para pegar o grupo que é pai do grupo cujo ID foi fornecido
     * 
     * @return String (SQL Query)
     */
    public function getGrupo_pai_by_id();
    
    /**
     * Retorna a Query para salvar(insert/update) o session_id
     * 
     * @return String (SQL Query)
     */
    public function salvar_session_id();
    
    /**
     * Retorna a Query para verificar o session_id
     * 
     * @return String (SQL Query)
     */
    public function verificar_session_id();

    /**
     * Retorna a Query para alterar o status da session
     *
     * @return string (SQL Query)
     */
    public function set_session_status();
    
    /**
     * Retorna a Query para pegar todos os cargos
     * 
     * @return String (SQL Query)
     */
    public function get_cargos();

    /**
     * Retorna a query para epgar toda a arvore de cargos(Cargo)
     * 
     * @return String (SQL Query)
     */
    public function get_arvore_cargos();

    /**
     * Retorna a Query para pegar todos os cargos (cargo) candidatos a pai de um determinado cargo
     * 
     * @return String (SQL Query)
     */
    public function get_cargos_candidatos_pai();
    
    /**
     * Retorna a Query para pegar o cargo pai de um determinado cargo
     * 
     * @return String (SQL Query)
     */
    public function get_cargo_pai_by_id();
    
    /**
     * Retorna a Query para pegar um cargo por ID
     * 
     * @return String (SQL Query)
     */
    public function get_cargo();
    
    /**
     * Retorna a Query para pegar todos os cargos inferiores a um determinado cargo
     * 
     * @return String (SQL Query)
     */
    public function get_sub_cargos();
    
    /**
     * Retorna a Query para pegar as permissoes de um cargo
     * 
     * @return String (SQL Query)
     */
    public function get_permissoes_cargo();
    
    /**
     * Retorna a Query para inserir um cargo
     * 
     * @return String (SQL Query)
     */
    public function inserir_cargo();
    
    /**
     * Retorna a Query para atualizar um cargo
     * 
     * @return String (SQL Query)
     */
    public function atualizar_cargo();
    
    /**
     * Retorna a Query para remover um cargo
     * 
     * @return String (SQL Query)
     */
    public function remover_cargo();
    
    /**
     * Retorna a Query para inserir uma permissao em um modulo para um cargo
     * 
     * @return unknown_type
     */
    public function inserir_permissao_modulo_cargo();
    
    /**
     * Retorna a Query para remover uma permissao em um modulo para um cargo
     * 
     * @return unknown_type
     */
    public function remover_permissao_modulo_cargo();
    
    /**
     * Retorna a Query para remover TODAS permissoes de um cargo
     * 
     * @return unknown_type
     */
    public function remover_permissoes_cargo();
    
    /**
     * Retorna a Query para pegar a lotacao de um usuario(Usuario) em um grupo(Grupo)
     * 
     * @return String (SQL Query)
     */
    public function getLotacao();
    
    /**
     * Retorna a Query para criar uma lotacao
     * 
     * @return String (SQL Query)
     */
    public function inserir_lotacao();

    /**
     * Retorna a Query para inserir um servico na unidade
     * 
     * @return String (SQL Query)
     */
    public function inserir_servico_uni();
    /**
     * Retorna a Query para alterar um servico na unidade
     * 
     * @return String (SQL Query)
     */
    public function alterar_servico_uni();
    /**
     * Retorna a Query para atualizar uma lotacao
     * 
     * @return String (SQL Query)
     */
    public function atualizar_lotacao();
    
    /**
     * Retorna a Query para remover uma lotacao
     * 
     * @return String (SQL Query)
     */
    public function remover_lotacao();
    
    /**
     * Retorna a Query para atualizar os dados de um grupo
     * 
     * @return String (SQL Query)
     */
    public function atualizar_grupo();
    
    /**
     * Retorna a Query para remover um grupo em cascata
     * 
     * @return String (SQL Query)
     */
    public function remover_grupo();
    
    /**
     * Retorna a Query para remover a senha de uma unidade
     * 
     * @return String (SQL Query)
     */
    
    public function remover_senha_uni_msg();

    /**
     * Retorna a Query para remover uma unidade
     * 
     * @return String (SQL Query)
     */
    public function remover_unidade();
    
    /**
     * Retorna a Query para remover um serviço da unidade
     * 
     * @return String (SQL Query)
     */
    public function remover_servico_uni();
    
    
    /**
     * Retorna a Query para pegar todos os grupos (Grupo) da Unidade
     * 
     * @return String (SQL Query)
     */
    /* DELETAR public function getGrupos_unidade();*/

    /**
     * Retorna a Query para pegar um array contendo o menu do modulo
     * @return String (SQL Query)
     */
    public function get_menu();
    
    /**
     * Retorna a Query para pegar uma String contendo o link menu especificado
     * 
     * @return String (SQL Query)
     */
    public function get_menu_link();

    /**
     * Retorna a Query para pegar o numero total de senhas distribuidas
     * 
     * @return String (SQL Query)
     */
    public function get_total_fila();

    /**
     * Retorna a Query para pegar a ultima senha da fila distribuida (gerada)
     * 
     * @return String (SQL Query)
     */
    public function get_ultima_senha();

    /**
     * Retorna a Query para pegar o numero da proxima senha a ser gerada
     * 
     * @return String (SQL Query)
     */
    public function reiniciar_senhas_unidade();

    /**
     * Retorna a Query para reiniciar as senhas
     *
     * @return String (SQL Query)
     */
    public function get_proxima_senha_numero();

    /**
     * Retorna a Query para pegar o Servico especificado pelo id do servico
     * 
     * @return String (SQL Query)
     */
    public function get_servico();
    
    /**
     * Retorna a Query para pegar o Servico especificado pelo id do servico
     * e pelo id da unidade atual
     * @return String (SQL Query)
     */
    public function get_servico_current_uni();
    
    /**
     * Retorna a Query para inserir um serviço global.
     * 
     * @return string (SQL Query)
     */
    public function inserir_servico();
    
    /**
     * Retorna a Query para atualizar um serviço global.
     *
     * @return string (SQL Query)
     */
    public function atualizar_servico();
    
    /**
     * Retorna a Query para atualizar o status do sub_servico.
     *
     * @return string (SQL Query)
     */
    public function atualizar_sub_servico();
    
    /**
     * Retorna a Query para atualizar um status do serviço na unidade.
     *
     * @return string (SQL Query)
     */
    public function atualiza_stat_uni_serv();

    /**
     * Retorna status de ums serviço.
     *
     * @return string (SQL Query)
     */
    public function get_stat_serv();
    
    /**
     * Retorna a Query para remvoer um serviço glboal.
     * 
     * @return string (SQL Query)
     */
    public function remover_servico();
    
    /**
     * Retorna a Query para pegar um array contendo todos servicos ativos do sistema
     * 
     * @return String (SQL Query)
     */
    public function getServicos();
    
    /**
     * Retorna a Query para pegar um array contendo todos servicos da unidade
     * 
     * @return String (SQL Query)
     */    
    public function getServicos_unidade();

    /**
     * Retorna a Query para pegar um array contendo os servicos mestre de uma unidade
     * 
     * @return String (SQL Query)
     */
    
    
    public function getServicos_mestre_unidade();
    
    /**
     * Retorna os serviços disponiveis das unidades e também o nome do serviço mestre à que se refere
     * @return String (SQL Query)
     */
    public function get_serv_disponiveis_uni();
    
    
    /**
     * Retorna a Query para pegar um array contendo os servicos mestre de uma unidade 
     * que possui senhas canceladas
     * @return String (SQL Query)
     */
    public function getServicos_unidade_reativar();
    

    /**
     * Retorna a Query para pegar um array contendo os servicos mestre do Sistema
     * 
     * @return String (SQL Query)
     */
    public function getServicos_mestre();

    /**
     * Retorna a Query para pegar um array contendo os subservicos do servico mestre
     * 
     * @return String (SQL Query)
     */
    public function getServicos_sub_unidade();
    
    /**
     * Retorna a Query para pegar um array contendo os sub servicos de um servico mestre do sistema
     * 
     * @return String (SQL Query)
     */
    public function getServicos_sub();

    /**
     * Retorna a Query para pegar a Fila de clientes de acordo com o(s) servico(s) especificado(s)
     * 
     * @return String (SQL Query)
     */
    public function get_fila();
    
    /**
     * Retorna a Query para pegar o próximo cliente na fila de acordo com o(s) servico(s) especificado(s)
     * 
     * @return String (SQL Query)
     */
    public function get_proximo_atendimento();
    
    /**
     * Retorna a Query para pegar o atendimento
     * 
     * @return String (SQL Query)
     */
    public function get_atendimento();
    
    /**
     * Retorna aquery para obter atendimentos de um usuario em determinados status 
     * 
     * @return String (SQL Query)
     */
    public function get_atendimentos_by_usuario();
    
    /**
     * Retorna a Query para pegar o atendimento
     * 
     * @return String (SQL Query)
     */
    public function get_atendimento_por_senha();
    
    /**
     * Retorna a Query para definir o status do Atendimento
     * 
     * @return string (SQL Query)
     */
    public function set_atendimento_status();
    
    /**
     * Retorna a Query para definir o status do Atendiment
     * 
     * @return string (SQL Query)
     */
    public function set_atendimento_prioridade();
    
    /**
     * Retorna a Query para definir o Usuario que esta efetuando o Atendimento
     * 
     * @return String (SQL Query)
     */
    public function set_atendimento_usuario();
    
    /**
     * Retorna a Query para definir o guiche em que o atendimento está sendo efetuado
      * 
      *@return String (SQL Query)
     */
    public function set_atendimento_guiche();
    /**
     * Retorna a Query para colocar uma nova senha para ser chamada pelo painel
     * 
     * @return String (SQL Query)
     */
    public function chama_proximo();

    /**
     * Retorna um array contendo as prioridades (Prioridade)
     * 
     * @return String (SQL Query)
     */
    public function get_prioridades();    

    /**
     * Retorna a Query para transferir a senha informada para o novo Servico, mudando
     * ou nao sua Prioridade
     * 
     * @return String (SQL Query)
     */
    public function transfere_senha();

    /**
     * Retorna a Query para inserir senha de atendimento
     * 
     * @return String (SQL Query)
    */
    public function distribui_senha();
    
    
    
    /**
     * Retorna a Query para confirmar o encerramento do atendimento
     * 
     * @return String (SQL Query)
     */
    public function encerra_atendimento();
    
    /**
     * Retorna quantidade total de clientes do servico
     * @return String (SQL Query)
     */
    public function quantidade_total();
    
    /**
     * Retorna quantidade de clientes na fila
     * @return String(SQL Query)
     */
    public function quantidade_fila();
    
    /**
     * Retorna Query para pegar mensagem que é exibida na senha
     * @return String(SQL Query)
     */
    public function get_senha_msg_loc();
    
    /**
     * Modifica a mensagem que é exibida na senha
     * @return String(SQL Query)
     */
    public function set_senha_msg_loc();
    
    /**
     * Retorna Query para pegar mensagem que é exibida na senha
     * @return String(SQL Query)
     */
    public function get_senha_msg_global();
    
    /**
     * Modifica a mensagem global
     * @return String(SQL Query)
     */
    public function set_senha_msg_global();
    
    
    public function set_senha_msg_global_unidades_locais();
    
    /**
     * 
     * @return lista de serviços passiveis de transferir senha
     */
    public function getServicos_unidade_transfere_senha();
    

    /**
     * 
     * @return lista de atendimentos de um determinado periodo
     */
    public function get_atendimento_senha_periodo();


    /**
     * Retorna Query para remover um serviço de um usuário em uma unidade
     * @return String(SQL Query)
     */
    public function remover_servico_usu();
        /**
     * Retorna Query para remover todos os serviços de um usuário em uma unidade
     * @return String(SQL Query)
     */
    public function remover_servicos_usu();
    
    /**
     * Retorna Query para adicionar um serviço a um usuário
     * @return String(SQL Query)
     */
    public function adicionar_servico_usu();
    /**
     * Retorna Query para alterar um usuário
     * @return String(SQL Query)
     */
    public function alterar_usu();
    
    /**
     * Retorna o nome do status
     * @return String (SQL Query)
     */
    public function getStatus();
    
    /**
     * Insere mensgaem na criação da unidade
     * @return String (SQL Query)
     */
    public function insere_mensagem();

    /**
     * Query para retornar os tempos medios de atendimento
     * @return String (SQL Query)
     */
    public function get_estat_tempos_medios();
    
    /**
     * Query para retornar a quantidade de senha em cada status
     * @return String (SQL Query)
     */
    public function get_qtde_senhas_por_status();
    
    /**
     * Query para obter as estatisticas por servico mestre
     * @return String (SQL Query)
     */
    public function get_estatistica_servico_mestres();
    
    /**
     * Query para obter as estatisticas por servicos codificados
     * @return String (SQL Query)
     */
    public function get_estatistica_servico_codificados();
    
    /**
     * Retorna se impressão está ativa ou não
     * @return String (SQL Query)
     */
    public function get_msg_status();
    
    /**
     * Modifica status da impressão
     * @return String (SQL Query)
     */
    public function set_msg_status();
    
    /**
     * Retorna nome da prioridade especificada
     * @return     String (SQL Query)
     */
    public function get_nm_pri();
    
    /**
     * Retorna apenas os serviços que 
     * não estão cadastrados em determinadas unidades
     * @return array
     */
    public function getServicos_macro_nao_cadastrados_uni();
    
    /**
     * Retorna apenas os sub-serviços que 
     * não estão cadastrados em determinadas unidades pelo macro servico
     * @return array
     */
    public function getServicos_sub_nao_cadastrados_uni();
    
    /**
     * Altera a senha do usuário
     * @return array
     */
    public function alterar_senha_usu();

    /**
     * Altera a senha do usuário no modulo usuario
     * @return array
     */
    public function alterar_senha_mod_usu();
    
    
    /**
     * Retorna usuarios pelo login(ou parte dele) informado
     * @return array
     */
    public function get_usuario_by_mat();

    /**
     * Retorna lista de serviços que um usuario nao atende
     * @return array Teste
     */
    public function getServicos_unidade_erro_triagem();
    
    /**
     * Modifica satus do usuário
     * @return String (SQL Query)
    */
    public function setStatus_usu();
    
    /**
     * Retorna status do usuario
     * @return String (SQL Query)
     */
    public function getStatus_usu();
    
    /**
     * Retorna status da unidade
     * @return String (SQL Query)
     */
    public function getStatus_uni();
    
    /**
     * Modifica status da unidade
     * @return String (SQL Query)
     */
    public function setStatus_uni();
    
    /*
     * Retorna os atendimentos com status encerrados
     * que estao dentro do período informado
     * @return String (SQL Query)
     */
    public function get_estat_atendimentos_encerradas();
    
    /**
     * Retorna os atendimentos que estão dentro do
     * periodo informado
     * @return String (SQL Query)
     */
    public function get_estat_atendimentos();

}
