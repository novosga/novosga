<?php

/**
 * Queries SQL ANSI
 *
 */
abstract class DefaultQueryProvider implements QueryProvider {
    
    const DATE_FORMAT_TIME     = 1; // hh:mm:ss
    const DATE_FORMAT_DATE     = 2; // mm/dd/yyyy
    const DATE_FORMAT_DATETIME = 3; // mm/dd/yyyy hh:mm:ss
    const DATE_FORMAT_YM = 4; // yyyy-mm
    
    protected abstract function dateToChar($field, $format);
    protected abstract function concat($exp1, $exp2);
    protected abstract function dateAvg($exp);
    
    
    public function has_mod_global_access() {
        return "
            SELECT 
                *
            FROM 
                usu_grup_cargo ugc
            INNER JOIN 
                cargos_mod_perm cmp
                ON (ugc.id_cargo = cmp.id_cargo)
            WHERE 
                ugc.id_usu = :id_usu AND 
                cmp.id_mod = :id_mod
        ";
    }
    
    public function salvarSessionId() {
        return $this->invokeProcedure('sp_salvar_session_id', array(':id_usu', ':session_id'));
    }

    public function setSessionStatus() {
        return "UPDATE usu_session SET stat_session = :stat_session WHERE id_usu = :id_usu";
    }

    public function verificarSessionId() {
        return "SELECT stat_session FROM usu_session WHERE id_usu = :id_usu AND session_id = :session_id";
    }
    
    /*
     * getting
     */
    
    protected function selectFrom($table, $as = "", $columns = "*") {
        return "SELECT $columns FROM $table $as";
    }
    
    /*
     * getting - prioridades
     */
    
    public function selectPrioridade() {
        return $this->selectFrom("prioridades") . " WHERE id_pri = :id";
    }
    
    public function selectPrioridades() {
        return $this->selectFrom("prioridades") . " ORDER BY nm_pri";
    }
    
    public function selectPrioridadesByNomeOrDescricao() {
        return $this->selectFrom("prioridades") . " WHERE UPPER(nm_pri) LIKE UPPER(:arg) OR  UPPER(desc_pri) LIKE UPPER(:arg) ORDER BY nm_pri";
    }
    
    /*
     * getting - usuarios
     */

    public function selectUsuarios() {
        return $this->selectFrom("usuarios") . " ORDER BY login_usu";
    }

    public function selectUsuario() {
        return $this->selectFrom("usuarios") . " WHERE id_usu = :id ORDER BY login_usu";
    }

    public function selectUsuarioByLogin() {
        return $this->selectFrom("usuarios") . " WHERE login_usu = :login ORDER BY login_usu";
    }

    public function selectUsuariosByLoginOrNome() {
        return $this->selectFrom("usuarios") . " WHERE UPPER(login_usu) LIKE UPPER(:arg) OR UPPER(" . $this->concat("nm_usu", "ult_nm_usu") . ") LIKE UPPER(:arg) ORDER BY login_usu";
    }
    
    /*
     * getting - modulos
     */

    public function selectModulo() {
        return $this->selectFrom("modulos") . " WHERE id_mod = :id";
    }

    public function selectModuloByChave() {
        return $this->selectFrom("modulos") . " WHERE chave_mod = :chave AND stat_mod = :status ORDER BY nm_mod ASC";
    }

    public function selectModulos() {
        return $this->selectFrom("modulos") . " WHERE stat_mod IN (:status) AND tipo_mod IN (:tipos) ORDER BY nm_mod ASC";
    }
    
    /*
     * getting - grupos
     */
    
    public function selectGrupo() {
        return $this->selectFrom("grupos_aninhados") . " WHERE id_grupo = :id";
    }
    
    public function selectGrupos() {
        return $this->selectFrom("grupos_aninhados") . " ORDER BY esquerda, nm_grupo";
    }
    
    public function selectGruposByNomeOrDescricao() {
        return $this->selectFrom("grupos_aninhados") . " WHERE UPPER(nm_grupo) LIKE UPPER(:arg) OR UPPER(desc_grupo) LIKE UPPER(:arg) ORDER BY esquerda, nm_grupo";
    }

    public function selectGruposPaiByFilho() {
        // os filhos estao entre a "esquerda" e "direita" do pai
        return "
            SELECT 
                pai.*
            FROM 
                grupos_aninhados AS pai
            LEFT JOIN
                grupos_aninhados AS filho
                ON 
                    filho.esquerda > pai.esquerda AND 
                    filho.direita < pai.direita
            WHERE 
                filho.id_grupo = :id 
            ORDER BY 
                pai.esquerda DESC
        ";
    }
    
    /*
     * getting - cargos
     */
    
    public function selectCargo() {
        return $this->selectFrom("cargos_aninhados") . " WHERE id_cargo = :id";
    }
    
    public function selectCargos() {
        return $this->selectFrom("cargos_aninhados") . " ORDER BY esquerda, nm_cargo";
    }
    
    public function selectCargosByNomeOrDescricao() {
        return $this->selectFrom("cargos_aninhados") . " WHERE UPPER(nm_cargo) LIKE UPPER(:arg) OR UPPER(desc_cargo) LIKE UPPER(:arg) ORDER BY esquerda, nm_cargo";
    }
    
    public function selectCargosPaiByFilho() {
        // os filhos estao entre a "esquerda" e "direita" do pai
        return "
            SELECT 
                pai.*
            FROM 
                cargos_aninhados AS pai
            LEFT JOIN
                cargos_aninhados AS filho
                ON 
                    filho.esquerda > pai.esquerda AND 
                    filho.direita < pai.direita
            WHERE 
                filho.id_cargo = :id 
            ORDER BY 
                pai.esquerda DESC
        ";
    }
    
    /*
     * getting - unidades
     */

    public function selectUnidade() {
        return $this->selectFrom("unidades") . " WHERE id_uni = :id";
    }
    
    public function selectUnidades() {
        return $this->selectFrom("unidades") . " ORDER BY nm_uni ASC";
    }

    public function selectUnidadesByCodigoOrNome() {
        return $this->selectFrom("unidades") . " WHERE UPPER(cod_uni) LIKE UPPER(:arg) OR UPPER(nm_uni) LIKE UPPER(:arg) ORDER BY nm_uni";
    }

    public function selectUnidadesByUsuario() {
        return $this->selectFrom("unidades", "u") . 
                " INNER JOIN usu_serv us ON u.id_uni = us.id_uni" .
                " WHERE us.id_usu = :id_usu ORDER BY nm_uni";
    }

    public function selectUnidadesVisiveis() {
        return $this->selectFrom("unidades") . "
                INNER JOIN 
                    grupos_aninhados g ON g.id_grupo = u.id_grupo
                WHERE 
                    u.id_uni IN (
                        SELECT 
                            id_uni
                        FROM 
                            unidades
                        INNER JOIN 
                            grupos_aninhados
                            ON grupos_aninhados.id_grupo = unidades.id_grupo
                        WHERE 
                            grupos_aninhados.id_grupo IN (
                                SELECT 
                                    folhas.id_grupo
                                FROM 
                                    grupos_aninhados pai
                                INNER JOIN 
                                    grupos_aninhados folhas ON 
                                        folhas.esquerda >= pai.esquerda AND 
                                        folhas.direita <= pai.direita
                                WHERE 
                                    pai.id_grupo IN (
                                        SELECT ugc.id_grupo FROM usu_grup_cargo ugc WHERE id_usu = :id_usu
                                    )
                            )
                    ) AND 
                    u.id_grupo IN (
                        SELECT DISTINCT 
                            filho.id_grupo
                        FROM 
                            grupos_aninhados pai, grupos_aninhados filho
                        WHERE 
                            filho.esquerda >= pai.esquerda AND 
                            filho.esquerda <= pai.direita AND 
                            filho.esquerda >= (
                                SELECT esquerda FROM grupos_aninhados WHERE id_grupo = :id_grupo_1
                            ) AND 
                            filho.direita <= (
                                SELECT direita FROM grupos_aninhados WHERE id_grupo = :id_grupo_2
                            ) AND 
                            pai.id_grupo IN (
                                SELECT 
                                    ugc.id_grupo
                                FROM 
                                    usu_grup_cargo ugc
                                INNER JOIN 
                                    cargos_mod_perm cmp
                                    ON ugc.id_cargo = cmp.id_cargo
                                WHERE 
                                    cmp.id_mod = :id_mod AND 
                                    cmp.permissao = 3 AND 
                                    id_usu = :id_usu_admin
                        )
                )";
    }
    
    /*
     * getting - menu
     */

    public function selectMenu() {
        return $this->selectFrom("menus") . " WHERE id_menu = :id_menu";
    }

    public function selectMenuByModulo() {
        return $this->selectFrom("menus") . " WHERE id_mod = :id_mod ORDER BY ord_menu ASC";
    }
    
    /*
     * getting - servicos
     */
    
    public function selectServico() {
        return $this->selectFrom("servicos") . " WHERE id_serv = :id";
    }
    
    public function selectServicos() {
        return $this->selectFrom("servicos", "s", "s.*") . " LEFT JOIN servicos m ON s.id_macro = m.id_serv ORDER BY COALESCE(m.nm_serv || s.nm_serv, s.nm_serv)";
    }
    
    public function selectServicosByNomeOrDescricao() {
        return $this->selectFrom("servicos") . " WHERE UPPER(nm_serv) LIKE UPPER(:arg) OR UPPER(desc_serv) LIKE UPPER(:arg) ORDER BY nm_serv ASC";
    }
    
    public function selectServicosMestre() {
        return $this->selectFrom("servicos") . " WHERE id_macro IS NULL ORDER BY nm_serv ASC";
    }
    
    public function selectServicosUnidade() {
        return $this->selectFrom("servicos", "s") . "
            INNER JOIN uni_serv us 
                ON s.id_serv = us.id_serv 
            WHERE
                us.id_uni = :id_uni AND us.stat_serv = 1
            ORDER BY
                s.nm_serv ASC
        ";
    }
    
    
    

    public function select_usuarios_grupos_by_usuario() {
        $cond = "
            SELECT 
                %column%
            FROM 
                grupos_aninhados filho
            INNER JOIN 
                grupos_aninhados pai
                ON (filho.esquerda >= pai.esquerda AND filho.direita <= pai.direita)
            INNER JOIN 
                usu_grup_cargo ugc
                ON (ugc.id_grupo = pai.id_grupo AND ugc.id_usu = :id_usu_1)
            INNER JOIN 
                cargos_aninhados adm_ca_pai
                ON (ugc.id_cargo = adm_ca_pai.id_cargo)
            INNER JOIN 
                cargos_aninhados adm_ca_filho
                ON (adm_ca_filho.esquerda >= adm_ca_pai.esquerda AND adm_ca_filho.direita <= adm_ca_pai.direita)
            WHERE 
                filho.esquerda >= (
                    SELECT esquerda FROM grupos_aninhados WHERE id_grupo = :id_grupo_1
                ) AND 
                filho.direita <= (
                    SELECT direita FROM grupos_aninhados WHERE id_grupo = :id_grupo_2
                ) AND 
                pai.id_grupo IN (
                    SELECT 
                        ugc.id_grupo
                    FROM 
                        usu_grup_cargo ugc
                    INNER JOIN 
                        cargos_mod_perm cmp
                        ON ugc.id_cargo = cmp.id_cargo
                    WHERE 
                        cmp.id_mod = :id_mod AND 
                        cmp.permissao = 3 AND 
                        id_usu = :id_usu_2
                )
        ";
        return "
            SELECT 
                *
            FROM 
                usuarios
            WHERE 
                login_usu LIKE :termo_login AND 
                nm_usu LIKE :termo_nome AND 
                id_usu IN (
                    SELECT 
                        id_usu
                    FROM 
                        usu_grup_cargo ugc_usu
                    INNER JOIN cargos_aninhados ca
                        ON (ugc_usu.id_cargo = ca.id_cargo)
                    WHERE 
                        ugc_usu.id_grupo IN (" . str_replace('%column%', 'filho.id_grupo', $cond) . ") AND
                        ugc_usu.id_cargo IN (" . str_replace('%column%', 'adm_ca_filho.id_cargo', $cond) . ")
                )
            ORDER BY 
                login_usu
        ";
    }

    public function select_lotacoes_visiveis() {
        $cond = "
            SELECT 
                %column%
            FROM 
                grupos_aninhados filho
            INNER JOIN 
                grupos_aninhados pai
                ON (filho.esquerda >= pai.esquerda AND filho.direita <= pai.direita)
            INNER JOIN 
                usu_grup_cargo ugc
                ON (ugc.id_grupo = pai.id_grupo AND ugc.id_usu = :id_usu_admin_1)
            INNER JOIN 
                cargos_aninhados adm_ca_pai
                ON (ugc.id_cargo = adm_ca_pai.id_cargo)
            INNER JOIN 
                cargos_aninhados adm_ca_filho
                ON (adm_ca_filho.esquerda >= adm_ca_pai.esquerda AND adm_ca_filho.direita <= adm_ca_pai.direita)
            WHERE 
                filho.esquerda >= (
                    SELECT esquerda FROM grupos_aninhados WHERE id_grupo = :id_grupo_1
                ) AND 
                filho.direita <= (
                    SELECT direita FROM grupos_aninhados WHERE id_grupo = :id_grupo_2
                ) AND 
                pai.id_grupo IN (
                    SELECT  
                        ugc.id_grupo 
                    FROM 
                        usu_grup_cargo ugc
                    INNER JOIN 
                        cargos_mod_perm cmp
                        ON ugc.id_cargo = cmp.id_cargo
                    WHERE 
                        cmp.id_mod = :id_mod AND 
                        cmp.permissao = 3 AND 
                        id_usu = :id_usu_admin_2
                )
        ";
        return "
            SELECT 
                *
            FROM 
                usu_grup_cargo ugc_usu
            INNER JOIN 
                cargos_aninhados ca
                ON (ugc_usu.id_cargo = ca.id_cargo)
            WHERE 
                id_usu = :id_usu AND 
                ugc_usu.id_grupo IN (" . str_replace('%column%', 'filho.id_grupo', $cond) . ") AND
                ugc_usu.id_cargo IN (" . str_replace('%column%', 'adm_ca_filho.id_cargo', $cond) . ")
        ";
    }

    public function select_usuario_servicos_unidade() {
        return "
            SELECT 
                usus.id_serv
            FROM 
                usu_serv usus
            INNER JOIN uni_serv unis
                ON (usus.id_serv = unis.id_serv AND usus.id_uni = unis.id_uni)
            INNER JOIN servicos s
                ON s.id_serv = unis.id_serv
            WHERE 
                usus.id_usu = :id_user AND 
                unis.id_uni = :id_uni AND 
                s.stat_serv IN (:status) AND 
                unis.stat_serv IN (:status)
            ORDER BY
                s.nm_serv ASC
        ";
    }

    public function select_arvore_grupos() {
        return "
            SELECT 
                no.*, pai.id_grupo as id_grupo_pai
            FROM 
                grupos_aninhados pai
            INNER JOIN (
                    SELECT 
                        no.id_grupo, 
                        MAX(pai.esquerda) as esquerda
                    FROM 
                        grupos_aninhados no
                    INNER JOIN 
                        grupos_aninhados pai
                        ON no.esquerda > pai.esquerda  AND no.esquerda < pai.direita
                    GROUP BY 
                        no.id_grupo
                ) SQ
                ON (pai.esquerda = SQ.esquerda)
            RIGHT OUTER JOIN 
                grupos_aninhados no
                ON (no.id_grupo = SQ.id_grupo)
            ORDER BY 
                no.esquerda
        ";
    }

    public function select_sub_grupos() {
        return "
            SELECT 
                id_grupo, nm_grupo, desc_grupo, esquerda
            FROM 
                grupos_aninhados 
            WHERE 
                esquerda > (
                    SELECT esquerda FROM grupos_aninhados WHERE id_grupo = :id_grupo_1 
                ) AND 
                direita < (
                    SELECT direita FROM grupos_aninhados WHERE id_grupo = :id_grupo_2 
                )
            ORDER BY 
                nm_grupo ASC
        ";
    }

    public function selectGrupos_candidatos_pai() {
        return "
            SELECT 
                id_grupo, nm_grupo, desc_grupo
            FROM 
                grupos_aninhados g
            WHERE 
                esquerda < (
                    SELECT esquerda FROM grupos_aninhados WHERE id_grupo = :id_grupo_1
                ) OR 
                direita > (
                    SELECT direita FROM grupos_aninhados WHERE id_grupo = :id_grupo_2
                )
            ORDER BY 
                nm_grupo ASC
        ";
    }

    public function selectGrupos_folha_disponiveis() {
        return "
            SELECT 
                DISTINCT (ga.id_grupo), 
                nm_grupo, 
                desc_grupo 
            FROM 
                grupos_aninhados AS ga
            WHERE 
                ga.direita = ga.esquerda + 1 AND
                ga.id_grupo NOT IN (
                    SELECT id_grupo FROM unidades
                )
            ORDER BY 
                nm_grupo ASC
        ";
    }

    public function select_lotacoes_editaveis() {
        return "
            SELECT 
                ga.*, 
                ugc.id_cargo
            FROM 
                usu_grup_cargo ugc
            INNER JOIN grupos_aninhados ga
                ON (ga.id_grupo = ugc.id_grupo)
            WHERE 
                ugc.id_usu = :id_usu AND 
                id_cargo IN (
                    SELECT id_cargo FROM cargos_mod_perm WHERE id_mod = :id_mod
                ) AND 
                ga.id_grupo IN (
                    SELECT id_grupo FROM grupos_aninhados WHERE esquerda >= (
                        SELECT esquerda FROM grupos_aninhados WHERE id_grupo = :id_grupo_1
                    ) AND 
                    direita <= (
                        SELECT direita FROM grupos_aninhados WHERE id_grupo = :id_grupo_2
                    )
                )
            ORDER BY 
                ga.esquerda ASC
        ";
    }

    public function selectGrupos_by_permissao_usuario() {
        return "SELECT DISTINCT no.id_grupo, no.nm_grupo, no.desc_grupo, no.esquerda
        FROM grupos_aninhados AS no,
        grupos_aninhados AS pai
        WHERE no.esquerda >= pai.esquerda
            AND no.esquerda <= pai.direita
            AND pai.id_grupo IN (
                SELECT ugc.id_grupo
                FROM usu_grup_cargo ugc
                INNER JOIN cargos_mod_perm cmp
                    ON (cmp.id_cargo = ugc.id_cargo)
                WHERE cmp.id_mod = :id_mod
                    AND cmp.permissao = 3
                    AND id_usu = :id_usu
            )
        ORDER BY no.esquerda";
    }

    public function select_cargos() {
        return "SELECT id_cargo, nm_cargo, desc_cargo, esquerda FROM cargos_aninhados ORDER BY nm_cargo";
    }

    public function select_arvore_cargos() {
        return "SELECT no.*, pai.id_cargo as id_cargo_pai
                FROM cargos_aninhados pai
                INNER JOIN
                    (
                        SELECT no.id_cargo, MAX(pai.esquerda) as esquerda
                        FROM cargos_aninhados no
                        INNER JOIN cargos_aninhados pai
                            ON no.esquerda > pai.esquerda  AND no.esquerda < pai.direita
                        GROUP BY no.id_cargo
                    ) SQ
                    ON (pai.esquerda = SQ.esquerda)
                RIGHT OUTER JOIN cargos_aninhados no
                    ON (no.id_cargo = SQ.id_cargo)
                ORDER BY no.esquerda";
    }

    public function select_cargos_candidatos_pai() {
        return "SELECT id_cargo, nm_cargo, desc_cargo, esquerda
        FROM cargos_aninhados g
        WHERE esquerda <
        (
        SELECT esquerda
        FROM cargos_aninhados
        WHERE id_cargo = :id_cargo_1
        )
        OR direita > 
        (
        SELECT direita
        FROM cargos_aninhados
        WHERE id_cargo = :id_cargo_2
        )
        ORDER BY nm_cargo ASC";
    }

    public function select_cargo_pai_by_id() {
        return "SELECT pai.id_cargo, pai.nm_cargo, pai.desc_cargo
FROM cargos_aninhados AS no,
cargos_aninhados AS pai
WHERE no.esquerda > pai.esquerda
    AND no.direita < pai.direita
AND no.id_cargo = :id_cargo_filho
ORDER BY pai.esquerda DESC
";
    }

    public function select_cargo() {
        return "SELECT id_cargo, nm_cargo, desc_cargo, esquerda FROM cargos_aninhados WHERE id_cargo = :id_cargo";
    }

    public function select_sub_cargos() {
        return "
            SELECT 
                id_cargo, nm_cargo, desc_cargo, esquerda
            FROM 
                cargos_aninhados 
            WHERE 
                esquerda >= (
                    SELECT esquerda FROM cargos_aninhados WHERE id_cargo = :id_cargo_1 
                ) AND 
                direita <= (
                    SELECT direita FROM cargos_aninhados WHERE id_cargo = :id_cargo_2
                )
            ORDER BY 
                nm_cargo ASC
        ";
    }

    public function select_permissoes_cargo() {
        return "
            SELECT 
                cmp.id_mod, cmp.permissao
            FROM 
                modulos m
            INNER JOIN 
                cargos_mod_perm cmp ON 
                m.id_mod = cmp.id_mod
            WHERE 
                cmp.id_cargo = :id_cargo
        ";
    }

    public function selectLotacao() {
        return "
            SELECT 
                c.id_cargo, c.nm_cargo, c.desc_cargo 
            FROM 
                cargos_aninhados c 
            WHERE id_cargo IN (
                SELECT 
                    id_cargo
                FROM 
                    usu_grup_cargo ugc
                WHERE 
                    ugc.id_usu = :id_usu AND 
                    ugc.id_grupo = :id_grupo
            )
        ";
    }
    
    /*
     * getting - atendimentos
     */
    
    public function selectAtendimentosByServicoUnidade() {
        return $this->selectFrom("atendimentos", "a") . " 
                INNER JOIN 
                    uni_serv us 
                    ON 
                        us.id_serv = a.id_serv AND 
                        us.id_uni = a.id_uni 
                INNER JOIN 
                    prioridades p 
                    ON 
                        p.id_pri = a.id_pri 
                WHERE 
                    a.id_serv = :id_serv AND 
                    a.id_uni = :id_uni
                ORDER BY 
                    p.peso_pri DESC, 
                    a.num_senha ASC
        ";
    }
    
    /*
     * inserting
     */

    public function insertGrupo() {
        return $this->invokeProcedure('sp_inserir_grupo', array(':id_pai', ':nome', ':descricao'));
    }

    public function insertCargo() {
        return $this->invokeProcedure('sp_inserir_cargo', array(':id_pai', ':nome', ':descricao'));
    }

    public function insertUnidade() {
        return "INSERT INTO unidades (id_grupo, cod_uni, nm_uni) VALUES (:id_grupo, :codigo, :nome)";
    }
    
    public function insertPrioridade() {
        return "INSERT INTO prioridades (nm_pri, desc_pri, peso_pri, stat_pri) VALUES (:nome, :descricao, :peso, :status)";
    }

    public function insertUsuario() {
        return "INSERT INTO usuarios (login_usu, nm_usu, ult_nm_usu, senha_usu, stat_usu) VALUES (:login, :nome, :sobrenome, :senha, 1)";
    }

    public function insertServico() {
        return "INSERT INTO servicos (id_macro, nm_serv, desc_serv, stat_serv) VALUES (:id_macro, :nome, :descricao, :status)";
    }

    public function insertServicoUnidade() {
        return "INSERT INTO uni_serv VALUES (:id_uni, :id_serv, :id_loc, :nome, :sigla, :status)";
    }

    public function insertLotacao() {
        return "INSERT INTO usu_grup_cargo VALUES (:id_usu, :id_grupo, :id_cargo)";
    }

    public function insertPermissaoModulo() {
        return "INSERT INTO cargos_mod_perm (id_cargo , id_mod , permissao) VALUES (:id_cargo, :id_mod, :permissao)";
    }
    
    /*
     * updating
     */

    public function updateGrupo() {
        return $this->invokeProcedure('sp_atualizar_grupo', array(':id', ':id_pai', ':nome', ':descricao'));
    }

    public function updateCargo() {
        return $this->invokeProcedure('sp_atualizar_cargo', array(':id', ':id_pai', ':nome', ':descricao'));
    }

    public function updateUsuario() {
        return "UPDATE usuarios SET login_usu = :login, nm_usu = :nome, ult_nm_usu = :sobrenome WHERE id_usu = :id";
    }

    public function updateUnidade() {
        return "UPDATE unidades SET id_grupo = :id_grupo, cod_uni = :codigo, nm_uni = :nome WHERE id_uni = :id";
    }

    public function updatePrioridade() {
        return "UPDATE prioridades SET nm_pri = :nome, desc_pri = :descricao, peso_pri = :peso, stat_pri = :status WHERE id_pri = :id";
    }

    public function updateServico() {
        return "UPDATE servicos SET id_macro = :id_macro, nm_serv = :nome, desc_serv = :descricao, stat_serv = :status WHERE id_serv = :id";
    }

    public function updateSubServicos() {
        return "UPDATE servicos SET stat_serv = :status WHERE id_macro = :id_macro";
    }

    public function updateServicoUnidade() {
        return "UPDATE uni_serv SET nm_serv = :nome, sigla_serv = :sigla, stat_serv = :status WHERE id_uni = :id_uni AND id_serv = :id";
    }

    public function updateLotacao() {
        return "UPDATE usu_grup_cargo SET id_cargo = :id_cargo, id_grupo = :id_grupo_novo WHERE id_usu = :id_usu AND id_grupo = :id_grupo";
    }

    public function updateStatusSubServicos() {
        return "UPDATE servicos SET stat_serv = :stat_serv WHERE id_macro = :id_serv";
    }
    
    /*
     * deleting
     */

    public function removerServico() {
        return "DELETE FROM servicos WHERE id_serv = :id_serv";
    }

    public function remover_servico_uni() {
        return "DELETE FROM uni_serv WHERE id_uni = :id_uni AND id_serv = :id_serv";
    }

    public function remover_permissao_modulo_cargo() {
        return "DELETE FROM cargos_mod_perm WHERE id_cargo = :id_cargo AND id_mod = :id_mod";
    }

    public function remover_permissoes_cargo() {
        return "DELETE FROM cargos_mod_perm WHERE id_cargo = :id_cargo";
    }

    public function removerLotacao() {
        return "DELETE FROM usu_grup_cargo WHERE id_usu = :id_usu AND id_grupo = :id_grupo";
    }

    public function removerLotacoes() {
        return "DELETE FROM usu_grup_cargo WHERE id_usu = :id_usu";
    }

    public function remover_senha_uni_msg() {
        return "DELETE FROM senha_uni_msg WHERE id_uni= :id_uni";
    }

    public function remover_unidade() {
        return "DELETE FROM unidades WHERE id_uni= :id_uni";
    }

    public function selectTotalFila() {
        return "SELECT COUNT(id_atend) FROM atendimentos WHERE id_stat = 1 AND id_uni = :id_uni";
    }

    public function selectUltimaSenha() {
        return "
            SELECT 
                num_senha, sigla_serv, id_atend
            FROM 
                atendimentos a 
            LEFT JOIN 
                uni_serv u 
                ON a.id_serv = u.id_serv AND a.id_uni = u.id_uni
            WHERE 
                id_stat IN (:ids_stat) AND 
                a.id_uni = :id_uni
            ORDER BY 
                num_senha DESC
            LIMIT 1
        ";
    }

    public function selectProximoAtendimento() {
        return "
            SELECT 
                a.id_atend, a.nm_cli, a.ident_cli, a.num_senha, a.id_pri, a.id_stat, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim,
                p.nm_pri, p.desc_pri, p.peso_pri, 
                us.sigla_serv, us.id_serv, us.nm_serv, 
                s.desc_serv
            FROM 
                atendimentos a
            INNER JOIN 
                uni_serv us
                ON us.id_serv = a.id_serv AND us.id_uni = a.id_uni
            INNER JOIN 
                servicos s
                ON us.id_serv = s.id_serv
            INNER JOIN 
                prioridades p
                ON p.id_pri = a.id_pri
            WHERE 
                s.id_serv in (:servicos) AND 
                us.stat_serv = 1 AND 
                a.id_stat = 1 AND a.id_uni = :id_uni
            ORDER BY 
                p.peso_pri DESC, a.num_senha ASC
        ";
    }
    
    /*
     * getting - servicos
     */

    public function selectServicosUnidadeByStatus() {
        return "
            SELECT  
                us.id_serv, us.nm_serv, us.sigla_serv, us.stat_serv, s.desc_serv
            FROM 
                uni_serv us 
            INNER JOIN servicos s 
                ON us.id_serv = s.id_serv
            WHERE 
                us.id_uni = :id_uni AND 
                us.stat_serv IN (:stats_serv) AND 
                s.stat_serv IN (:stats_serv)
            ORDER BY 
                us.nm_serv ASC
        ";
    }

    public function selectServicos_mestre_unidade() {
        return "
            SELECT 
                * 
            FROM 
                uni_serv us
            INNER JOIN servicos s 
                ON us.id_serv = s.id_serv
            WHERE 
                us.id_uni = :id_uni AND 
                s.id_macro IS NULL AND 
                s.stat_serv IN (:stats_serv) AND 
                us.stat_serv IN (:stats_serv)
            ORDER BY 
                us.nm_serv ASC
        ";
    }

    public function select_serv_disponiveis_uni() {
        return "
                SELECT 
                    us.id_serv, us.nm_serv, us.stat_serv, s.nm_serv AS nm_serv_mestre
                FROM 
                    uni_serv us
                INNER JOIN servicos s 
                    ON us.id_serv = s.id_serv
                WHERE 
                    us.id_uni = :id_uni AND 
                    s.stat_serv IN (:stats_serv) AND 
                    us.stat_serv IN (:stats_serv)
                ORDER BY 
                    us.nm_serv ASC
            ";
    }

    public function selectServicos_unidade_transfere_senha() {
        return "SELECT * 
        FROM uni_serv us
        INNER JOIN servicos s 
ON us.id_serv = s.id_serv
        WHERE us.id_uni = :id_uni
AND us.id_serv != :id_serv
                    AND s.stat_serv IN (:stats_serv)
AND us.stat_serv IN (:stats_serv)
        ORDER BY us.nm_serv ASC";
    }

    public function selectServicos_unidade_reativar() {
        return "
            SELECT *
            FROM servicos s
            INNER JOIN uni_serv us
                ON s.id_serv = us.id_serv
            WHERE 
                us.id_uni = :id_uni AND 
                s.stat_serv IN (:stats_serv) AND 
                us.stat_serv IN (:stats_serv) AND 
                s.id_serv IN (
                    SELECT id_serv
                    FROM atendimentos a
                    WHERE a.id_stat IN (:id_stat)
                    AND id_uni= :id_uni
                )
        ";
    }

    public function selectServicos_sub_unidade() {
        return "SELECT * FROM uni_serv AS us, servicos AS s
WHERE us.id_serv = s.id_serv
AND s.id_macro = :mestre
AND s.stat_serv IN (:stats_serv)
AND us.stat_serv IN (:stats_serv)
AND us.id_uni = :id_uni
AND s.id_macro IS NOT NULL
ORDER BY us.nm_serv ASC";
    }

    public function selectServicos_sub_nao_cadastrados_uni() {
        return "
            SELECT 
                * 
            FROM 
                servicos s
            WHERE 
                s.id_serv NOT IN (
                    SELECT id_serv FROM uni_serv WHERE id_uni = :id_uni
                ) AND 
                s.stat_serv = 1 AND 
                s.id_macro IS NOT NULL
            ORDER BY 
                s.id_macro, s.nm_serv
        ";
    }

    public function selectServicos_sub() {
        return "
            SELECT 
                * 
            FROM 
                servicos AS s
            WHERE 
                s.id_macro = :id_macro AND 
                s.id_macro IS NOT NULL AND 
                s.stat_serv IN (:stats_serv)
            ORDER BY 
                s.desc_serv ASC
        ";
    }

    public function select_atendimentos_by_usuario() {
        return "SELECT a.id_atend, a.nm_cli, a.num_senha, a.id_pri, a.id_stat, a.dt_cha, a.dt_ini, a.dt_fim, 
                    p.nm_pri, p.desc_pri, p.peso_pri, us.sigla_serv, us.id_serv, us.nm_serv, s.desc_serv 
FROM atendimentos a 
INNER JOIN uni_serv us 
ON us.id_serv = a.id_serv
AND us.id_uni = a.id_uni
INNER JOIN servicos s 
            ON us.id_serv = s.id_serv
INNER JOIN prioridades p 
ON p.id_pri=a.id_pri 
WHERE a.id_usu = :id_usu
AND a.id_uni = :id_uni
AND a.id_stat in (:status)";
    }

    public function select_atendimento_por_senha() {
        return "SELECT a.id_atend, a.nm_cli, a.num_senha, a.id_pri, a.id_stat, a.dt_cha, a.dt_ini, a.dt_fim, 
                    p.nm_pri, p.desc_pri, p.peso_pri, s.sigla_serv, s.id_serv , dt_cheg
FROM atendimentos a 
INNER JOIN uni_serv s 
ON s.id_serv=a.id_serv
                            AND s.id_uni = a.id_uni
INNER JOIN prioridades p 
ON p.id_pri=a.id_pri 
WHERE a.num_senha = :num_senha
AND a.id_uni = :id_uni
AND a.id_stat = :id_stat
";
    }

    public function select_atendimento() {
        return "SELECT a.id_atend, a.nm_cli, a.num_senha, a.id_pri, a.id_stat, a.dt_cha, a.dt_ini, a.dt_fim, 
                    p.nm_pri, p.desc_pri, p.peso_pri, s.sigla_serv, s.id_serv 
FROM atendimentos a 
INNER JOIN uni_serv s 
ON s.id_serv = a.id_serv
AND a.id_uni = s.id_uni
INNER JOIN prioridades p 
ON p.id_pri = a.id_pri 
WHERE a.id_atend = :id_atendimento
";
    }

    public function select_fila() {
        // id_stat = 1 -> passou pela triagem
        return "
            SELECT 
                a.id_atend, 
                a.nm_cli, 
                a.num_senha, 
                a.id_pri, 
                a.id_stat, 
                {$this->dateToChar('a.dt_cha', self::DATE_FORMAT_DATE)} as dt_cha, 
                {$this->dateToChar('a.dt_cheg', self::DATE_FORMAT_TIME)} as dt_cheg,
                {$this->dateToChar('a.dt_ini', self::DATE_FORMAT_TIME)} as dt_ini, 
                {$this->dateToChar('a.dt_fim', self::DATE_FORMAT_TIME)} as dt_fim,  
                p.nm_pri, 
                p.desc_pri, 
                p.peso_pri, 
                us.sigla_serv, 
                us.id_serv
            FROM 
                atendimentos a 
            INNER JOIN 
                uni_serv us
                ON us.id_serv = a.id_serv AND a.id_uni = us.id_uni
            INNER JOIN 
                servicos s
                    ON s.id_serv = us.id_serv
            INNER JOIN 
                prioridades p 
                    ON p.id_pri = a.id_pri
            WHERE 
                us.id_serv IN (:servicos) AND 
                s.stat_serv = 1 AND 
                us.stat_serv = 1 AND 
                us.id_uni = :id_uni AND 
                a.id_stat IN (:id_stat)
            ORDER BY 
                p.peso_pri DESC, 
                a.num_senha ASC
            ";
    }

    public function set_atendimento_status() {
        return "UPDATE atendimentos SET id_stat = :status, :column = :dt_time WHERE id_atend= :id_atend";
    }

    public function set_atendimento_prioridade() {
        return "UPDATE atendimentos SET id_pri = :id_pri WHERE id_atend = :id_atend";
    }

    public function set_atendimento_usuario() {
        return "UPDATE atendimentos SET id_usu = :id_usu WHERE id_atend= :id_atend";
    }

    public function set_atendimento_guiche() {
        return "UPDATE atendimentos SET num_guiche = :num_guiche WHERE id_atend = :id_atend";
    }

    public function chamaProximo() {
        return "
            INSERT INTO painel_senha 
            (id_uni, id_serv, num_senha, sig_senha, msg_senha, nm_local, num_guiche) 
            VALUES 
            (:id_uni, :id_serv, :num_senha, :sig_senha, :msg_senha, :nm_local, :num_guiche)
        ";
    }

    public function select_prioridades() {
        return "SELECT * FROM prioridades ORDER BY stat_pri, desc_pri ASC";
    }

    public function transfere_senha() {
        return "UPDATE atendimentos SET id_serv = :servico, id_pri = :prioridade WHERE id_atend = :id_atend";
    }

    public function encerra_atendimento() {
        return "INSERT INTO atend_codif (id_atend, id_serv, valor_peso) VALUES (:id_atend, :id_serv, :valor_peso)";
    }

    public function quantidade_total() {
        return "SELECT count(id_serv) FROM atendimentos WHERE id_uni= :id_uni AND id_serv= :id_serv";
    }

    public function quantidade_fila() {
        return "SELECT count(id_serv) FROM atendimentos WHERE id_uni= :id_uni AND id_serv= :id_serv AND id_stat= :id_stat";
    }

    public function select_senha_msg_loc() {
        return "SELECT msg_local FROM senha_uni_msg WHERE id_uni = :id_uni ";
    }

    public function set_senha_msg_loc() {
        return "UPDATE senha_uni_msg SET id_usu = :id_usu , msg_local = :msg WHERE id_uni = :id_uni ";
    }

    public function select_senha_msg_global() {
        return "SELECT msg_global FROM senha_uni_msg LIMIT 1";
    }

    public function set_senha_msg_global() {
        return "UPDATE senha_uni_msg SET id_usu = :id_usu , msg_global = :msg";
    }

    public function set_senha_msg_global_unidades_locais() {
        return "UPDATE senha_uni_msg SET id_usu = :id_usu , msg_global = :msg , msg_local = :msg";
    }

    public function remover_servico_usu() {
        return "DELETE FROM usu_serv WHERE id_uni = :id_uni AND id_usu = :id_usu AND id_serv = :id_serv";
    }

    public function remover_servicos_usu() {
        return "DELETE FROM usu_serv WHERE id_uni = :id_uni AND id_usu = :id_usu";
    }

    public function adicionar_servico_usu() {
        return "INSERT INTO usu_serv VALUES(:id_uni,:id_serv,:id_usu)";
    }

    public function select_atendimento_senha_periodo() {
        return "
            SELECT 
                (
                    SELECT ARRAY(
                        SELECT 
                            id_serv
                        FROM 
                            view_historico_atend_codif
                        WHERE 
                            id_atend = a.id_atend
                    )
                ) as id_servicos, 
                a.id_atend, 
                a.ident_cli, 
                a.num_senha, 
                a.id_stat, 
                {$this->dateToChar('a.dt_cheg', self::DATE_FORMAT_DATETIME)} as dt_cheg,
                {$this->dateToChar('a.dt_ini', self::DATE_FORMAT_DATETIME)} as dt_ini,
                {$this->dateToChar('a.dt_fim', self::DATE_FORMAT_DATETIME)} as dt_fim,
                p.nm_pri, 
                p.id_pri, 
                p.peso_pri, 
                u.login_usu, 
                a.num_guiche, 
                s.sigla_serv
            FROM 
                view_historico_atendimentos a
            INNER JOIN
                (
                    SELECT 
                        id_atend, id_serv
                    FROM 
                        view_historico_atendimentos  
                    WHERE 
                        num_senha = :num_senha AND 
                        id_uni = :id_uni AND 
                        dt_cheg >= :dt_ini AND 
                        dt_cheg <= :dt_fim
                ) atendimentos 
                ON a.id_atend = atendimentos.id_atend
            LEFT OUTER JOIN usuarios u
                ON u.id_usu = a.id_usu
            INNER JOIN uni_serv s 
                ON s.id_serv=a.id_serv AND s.id_uni = a.id_uni
            INNER JOIN prioridades p 
                ON p.id_pri = a.id_pri 
        ";
    }

    public function alterar_usu() {
        return "UPDATE usuarios SET nm_usu = :nm_usu, login_usu = :login_usu, ult_nm_usu = :ult_nm_usu WHERE id_usu = :id_usu";
    }

    public function selectStatus() {
        return "SELECT nm_stat FROM atend_status WHERE id_stat = :id_stat";
    }

    public function insere_mensagem() {
        return "INSERT INTO senha_uni_msg (id_uni,id_usu,msg_global) VALUES (:id_uni,:id_usu,:msg_global)";
    }

    public function select_estat_tempos_medios() {
        return "
            SELECT 
                COUNT(id_atend) as count_atend, 
                " . $this->dateToChar($this->dateAvg("dt_cha - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_espera,
                " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_total
            FROM 
                view_historico_atendimentos
            WHERE 
                dt_cheg >= :dt_min AND 
                dt_cheg <= :dt_max AND 
                id_stat = :id_stat AND 
                id_uni IN (:ids_uni)
        ";
    }

    public function select_qtde_senhas_por_status() {
        return "
            SELECT 
                id_stat, COUNT(id_stat) as count
            FROM 
                view_historico_atendimentos
            WHERE 
                dt_cheg >= :dt_min AND 
                dt_cheg <= :dt_max AND 
                id_uni IN (:ids_uni)
            GROUP BY 
                id_stat
        ";
    }

    public function select_estatistica_servico_mestres() {
        return "
            SELECT 
                coalesce(s.id_macro, s.id_serv) as id_macro,  
                s.nm_serv, 
                COUNT(COALESCE(s.id_macro, s.id_serv)) as count,
                " . $this->dateToChar($this->dateAvg("dt_cha - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_espera,
                " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_total
            FROM 
                view_historico_atendimentos a
            INNER JOIN 
                servicos s 
                ON a.id_serv = s.id_serv
            WHERE 
                a.dt_cheg >= :dt_min AND 
                a.dt_cheg <= :dt_max AND 
                a.id_stat = :id_stat AND 
                a.id_uni IN (:ids_uni) AND 
                s.id_macro is null
            GROUP BY 
                coalesce(s.id_macro, s.id_serv), 
                s.nm_serv
        ";
    }

    public function select_estatistica_servico_codificados() {
        return "
            SELECT 
                s.nm_serv, 
                COUNT(COALESCE(s.id_macro, s.id_serv)) as count,
                " . $this->dateToChar($this->dateAvg("dt_cha - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_espera,
                " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_total
            FROM 
                view_historico_atend_codif ac
            INNER JOIN 
                view_historico_atendimentos a 
                ON ac.id_atend = a.id_atend
            INNER JOIN 
                servicos s 
                ON ac.id_serv = s.id_serv
            WHERE 
                a.dt_cheg >= :dt_min AND 
                a.dt_cheg <= :dt_max AND 
                a.id_stat = :id_stat AND 
                a.id_uni IN (:ids_uni)
            GROUP BY 
                s.nm_serv
        ";
    }

    public function select_tempos_atend_por_usu() {
        return "
            SELECT 
                " . $this->concat('u.nm_usu', $this->concat("' '", 'u.ult_nm_usu')) . " AS nome, 
                SQ.count_atend, SQ.qtde_senhas, SQ.avg_desloc, SQ.avg_atend
            FROM 
                usuarios u
            INNER JOIN (
                SELECT 
                    a.id_usu, 
                    count(DISTINCT a.id_atend) AS qtde_senhas, 
                    count(ac.id_atend) AS count_atend,
                    " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                    " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend
                FROM 
                    view_historico_atendimentos a
                INNER JOIN 
                    view_historico_atend_codif ac
                    ON ac.id_atend = a.id_atend
                WHERE 
                    a.dt_cheg >= :dt_min AND 
                    a.dt_cheg <= :dt_max AND 
                    a.id_stat = :id_stat AND 
                    a.id_uni IN (:ids_uni)
                GROUP BY 
                    a.id_usu
                ) SQ
                ON (u.id_usu = SQ.id_usu)
        ";
    }

    public function select_estat_atend_por_usu() {
        return "
            SELECT 
                " . $this->concat('u.nm_usu', $this->concat("' '", 'u.ult_nm_usu')) . "  AS nome, 
                s.nm_serv, SQ.count_atend
            FROM 
                usuarios u
            INNER JOIN (
                SELECT 
                    a.id_usu, ac.id_serv, 
                    COUNT(ac.id_serv) AS count_atend
                FROM 
                    view_historico_atendimentos a
                INNER JOIN 
                    view_historico_atend_codif ac
                    ON a.id_atend = ac.id_atend
                WHERE 
                    dt_cheg >= :dt_min AND 
                    dt_cheg <= :dt_max AND 
                    id_stat = :id_stat AND 
                    id_uni IN (:ids_uni)
                GROUP BY 
                    a.id_usu, ac.id_serv
                ) SQ
                ON (u.id_usu = SQ.id_usu)
            INNER JOIN 
                servicos s
                ON (SQ.id_serv = s.id_serv)
            ORDER BY 
                nome
        ";
    }

    public function select_msg_status() {
        return "SELECT status_imp FROM senha_uni_msg WHERE id_uni = :id_uni";
    }

    public function set_msg_status() {
        return "UPDATE senha_uni_msg SET status_imp = :status_imp WHERE id_uni = :id_uni";
    }

    public function select_nm_pri() {
        return "SELECT nm_pri FROM prioridades WHERE id_pri = :id_pri";
    }

    public function selectServicos_macro_nao_cadastrados_uni() {
        return "
            SELECT 
                * 
            FROM 
                servicos 
            WHERE 
                id_serv NOT IN (
                    SELECT 
                        id_serv 
                    FROM 
                        uni_serv 
                    WHERE 
                        id_uni = :id_uni
                ) AND 
                stat_serv = 1 AND 
                id_macro is null
            ORDER BY 
                nm_serv
        ";
    }

    public function alterar_senha_usu() {
        return "UPDATE usuarios SET senha_usu = :nova_senha WHERE id_usu = :id_usu AND senha_usu = :senha_atual";
    }

    public function alterar_senha_mod_usu() {
        return "UPDATE usuarios SET senha_usu = :nova_senha WHERE id_usu = :id_usu";
    }

    public function selectServicos_unidade_erro_triagem() {
        return "
            SELECT 
                * 
            FROM 
                uni_serv us 
            INNER JOIN 
                servicos s 
                ON us.id_serv = s.id_serv 
            WHERE 
                us.id_uni = :id_uni AND 
                us.stat_serv IN (:stats_serv) AND 
                s.id_serv NOT IN (
                    SELECT id_serv 
                    FROM usu_serv 
                    WHERE 
                        id_usu = :id_usu AND 
                        id_uni = :id_uni
                )
            ORDER BY 
                us.nm_serv ASC
        ";
    }

    public function setStatus_usu() {
        return "UPDATE usuarios SET stat_usu = :stat_usu WHERE id_usu = :id_usu";
    }

    public function select_estat_atendimentos_encerradas() {
        return "
            SELECT 
                a.num_senha, 
                a.id_uni, 
                qtd_serv.count, 
                a.nm_cli, 
                " . $this->dateToChar('a.dt_cheg', self::DATE_FORMAT_DATE) . " as dt_cheg,
                " . $this->dateToChar('a.dt_ini', self::DATE_FORMAT_TIME) . " as dt_ini,
                " . $this->dateToChar('a.dt_fim', self::DATE_FORMAT_TIME) . " as dt_fim,
                " . $this->dateToChar('a.dt_cha', self::DATE_FORMAT_TIME) . " as dt_cha,
                " . $this->dateToChar('(dt_fim - dt_ini)', self::DATE_FORMAT_TIME) . " as tempo,
                s.nm_serv,
                u.login_usu, 
                a.num_guiche, 
                uni.nm_uni
            FROM 
                view_historico_atendimentos a
            LEFT OUTER JOIN 
                usuarios u
                ON u.id_usu = a.id_usu
            INNER JOIN 
                uni_serv s 
                ON s.id_serv = a.id_serv AND s.id_uni = a.id_uni
            INNER JOIN (
                    SELECT 
                        id_atend, COUNT(id_serv) as count
                    FROM 
                        view_historico_atend_codif
                    GROUP BY 
                        id_atend
                ) qtd_serv
                ON a.id_atend = qtd_serv.id_atend
            INNER JOIN 
                unidades uni
                ON a.id_uni = uni.id_uni
            WHERE 
                a.id_stat = 8 AND 
                a.dt_cheg >= :dt_min AND 
                a.dt_cheg <= :dt_max AND 
                a.id_uni IN (:ids_uni)
            ORDER BY 
                a.id_uni, a.num_senha, a.dt_cheg
        ";
    }

    public function select_estat_atendimentos() {
        return "
            SELECT 
                a.num_senha, a.id_serv, a.nm_cli, 
                s.nm_serv, s.id_uni, 
                uni.nm_uni, 
                st.nm_stat, u.login_usu, a.num_guiche,
                " . $this->dateToChar('a.dt_cheg', self::DATE_FORMAT_DATE) . " as dt_cheg,
                " . $this->dateToChar('a.dt_ini', self::DATE_FORMAT_TIME) . " as hr_ini,
                " . $this->dateToChar('a.dt_fim', self::DATE_FORMAT_TIME) . " as hr_fim,
                " . $this->dateToChar('a.dt_cha', self::DATE_FORMAT_TIME) . " as hr_cha,
                " . $this->dateToChar('a.dt_cheg', self::DATE_FORMAT_TIME) . " as hr_cheg,
                " . $this->dateToChar('(dt_fim - dt_ini)', self::DATE_FORMAT_TIME) . " as tmp_atend,
                " . $this->dateToChar('(dt_cha - dt_cheg)', self::DATE_FORMAT_TIME) . " as tmp_fila,
                " . $this->dateToChar('(dt_ini - dt_cha)', self::DATE_FORMAT_TIME) . " as tmp_desl,
                " . $this->dateToChar('(dt_fim - dt_cheg)', self::DATE_FORMAT_TIME) . " as tmp_total
            FROM 
                view_historico_atendimentos a
            LEFT OUTER JOIN 
                usuarios u
                ON u.id_usu = a.id_usu
            INNER JOIN 
                uni_serv s 
                ON s.id_serv = a.id_serv AND 
                s.id_uni = a.id_uni
            INNER JOIN 
                unidades uni
                ON uni.id_uni = a.id_uni
            INNER JOIN atend_status st
                ON a.id_stat = st.id_stat
            WHERE 
                a.dt_cheg >= :dt_min AND 
                a.dt_cheg <= :dt_max AND 
                a.id_uni IN (:ids_uni)
            ORDER BY 
                a.id_uni, a.num_senha, a.dt_cheg
        ";
    }

    public function select_ranking_unidades() {
        return "
            SELECT 
                u.nm_uni, SQ.count_atend, SQ.avg_espera, SQ.avg_desloc, SQ.avg_atend, SQ.avg_total
            FROM 
                unidades u
            INNER JOIN (
                SELECT 
                    id_uni, count(id_atend) as count_atend,
                    " . $this->dateToChar($this->dateAvg("dt_cha - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_espera,
                    " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                    " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend,
                    " . $this->dateToChar($this->dateAvg("dt_fim - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_total
                FROM 
                    view_historico_atendimentos vha
                WHERE 
                    dt_cheg >= :dt_min AND 
                    dt_cheg <= :dt_max AND 
                    id_stat = :id_stat AND 
                    vha.id_uni IN (:ids_uni)
                GROUP BY 
                    id_uni
            ) SQ
            ON (u.id_uni = SQ.id_uni)
        ";
    }

    public function select_estat_macro_serv_global() {
        return "
            SELECT 
                s.nm_serv, SQ.count_serv
            FROM 
                servicos s
            INNER JOIN (
                SELECT 
                    s.id_serv, COUNT(vha.id_serv) as count_serv
                FROM 
                    view_historico_atendimentos vha
                INNER JOIN 
                    servicos s
                    ON (s.id_serv = vha.id_serv)
                WHERE 
                    dt_cheg >= :dt_min AND 
                    s.id_macro IS NULL AND 
                    dt_cheg <= :dt_max AND 
                    id_stat = :id_stat AND 
                    vha.id_uni IN (:ids_uni)
                GROUP BY 
                    s.id_serv
            ) SQ 
            ON (SQ.id_serv = s.id_serv)
        ";
    }

    public function select_estat_atendimentos_uni_global() {
        return "
            SELECT 
                u.nm_uni, SQ.count_serv
            FROM 
                unidades u
            INNER JOIN (
                SELECT 
                    id_uni, COUNT(vha.id_serv) as count_serv
                FROM 
                    view_historico_atendimentos vha
                WHERE 
                    dt_cheg >= :dt_min AND 
                    dt_cheg <= :dt_max AND 
                    id_stat = :id_stat AND 
                    vha.id_uni IN (:ids_uni)
                GROUP BY 
                    id_uni
            ) SQ 
            ON (SQ.id_uni = u.id_uni)
        ";
    }

    public function select_estat_serv_uni() {
        return "
            SELECT 
                u.nm_uni, us.nm_serv, SQ.count_serv
            FROM 
                uni_serv us
            INNER JOIN (
                SELECT 
                    id_uni, id_serv, COUNT(vha.id_serv) as count_serv
                FROM 
                    view_historico_atendimentos vha
                WHERE 
                    dt_cheg >= :dt_min AND 
                    dt_cheg <= :dt_max AND 
                    id_stat = :id_stat AND 
                    vha.id_uni IN (:ids_uni)
                GROUP BY 
                    id_uni, id_serv
            ) SQ 
                ON (SQ.id_serv = us.id_serv AND SQ.id_uni = us.id_uni)
            INNER JOIN 
                unidades u
                ON (u.id_uni = SQ.id_uni)
            ORDER BY 
                nm_uni
        ";
    }

    public function select_tempos_medios_por_periodo() {
        $dt_atend = $this->dateToChar("dt_cheg", self::DATE_FORMAT_YM);
        return "
            SELECT 
                count(id_atend) as count_atend,
                $dt_atend as dt_atend,
                " . $this->dateToChar($this->dateAvg("dt_cha - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_espera,
                " . $this->dateToChar($this->dateAvg("dt_ini - dt_cha"), self::DATE_FORMAT_TIME) . " as avg_desloc,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_ini"), self::DATE_FORMAT_TIME) . " as avg_atend,
                " . $this->dateToChar($this->dateAvg("dt_fim - dt_cheg"), self::DATE_FORMAT_TIME) . " as avg_total
            FROM 
                view_historico_atendimentos vha
            WHERE 
                dt_cheg >= :dt_min AND 
                dt_cheg <= :dt_max AND 
                id_stat = :id_stat AND 
                vha.id_uni IN (:ids_uni)
            GROUP BY 
                $dt_atend
            ORDER BY 
                $dt_atend
        ";
    }
    
    /*
     * functions or procedures
     */
    protected function invokeProcedure($name, array $params) {
        throw new Exception(sprintf(_('Chamada a procedure/function "%s" não implementada'), $name));
    }

    public function remover_grupo() {
        return $this->invokeProcedure('sp_remover_grupo_cascata', array(':id_grupo'));
    }

    public function selectLotacao_valida() {
        return $this->invokeProcedure('sp_get_lotacao_valida', array(':id_usu', ':id_grupo'));
    }


    public function remover_cargo() {
        return $this->invokeProcedure('sp_remover_cargo_cascata', array(':id_cargo'));
    }

    public function reiniciar_senhas_unidade() {
        return $this->invokeProcedure('sp_acumular_atendimentos_unidade', array(':id_uni', ':dt_max'));
    }

    public function reiniciar_senhas_global() {
        return $this->invokeProcedure('sp_acumular_atendimentos', array(':dt_max'));
    }
    
    /*
     * util - triagem
     */

    public function distribuirSenha() {
        return "
            INSERT INTO atendimentos
            (id_uni, id_serv, id_pri, id_stat, nm_cli, num_guiche, dt_cheg, ident_cli, num_senha)
            -- select dentro do insert para garantir atomicidade
            SELECT
                :id_uni, :id_serv, :id_pri, :id_stat, :nm_cli, :num_guiche, :dt_cheg, :ident_cli,
                COALESCE(
                    (
                        SELECT TOP 1
                            num_senha
                        FROM 
                            atendimentos a 
                        WHERE
                            a.id_uni = :id_uni
                        ORDER BY
                            num_senha DESC
                    ) , 0) + 1
        ";
    }

}
