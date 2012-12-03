<?php

use \core\SGA;
use \core\util\Arrays;

/**
 * Default Database Adapter
 *
 */
abstract class DefaultDatabaseAdapter implements DatabaseAdapter {
    
    /** 
     * @var QueryProvider
     */
    protected $queryProvider;
    
    /** 
     * Connection
     * @var Connection
     */
    protected $conn;
    
    
    public function __construct() {
        $this->queryProvider = $this->createQueryProvider();
    }
    
    /**
     * Conecta ao banco
     */
    public function connect($host, $port, $user, $pass, $dbname) {
        try {
            $this->conn = new Connection($this->dsn($host, $port, $user, $pass, $dbname), $user, $pass);
            $this->queryProvider = $this->createQueryProvider();
        } catch (PDOException $e) {
            $e2 = new SGAException($e->getMessage(), $e->getCode());
            $e2->setShowTrace(false); // nao exibir stacktrace porque exibe a senha
            throw $e2;
        }
    }
    
    /**
     * PDO connection string
     * @return string
     */
    public abstract function dsn($host, $port, $user, $pass, $dbname);
    
    /**
     * @return QueryProvider
     */
    protected abstract function createQueryProvider();
    
    public function begin() {
        $this->getConnection()->beginTransaction();
    }
    
    public function commit() {
        $this->getConnection()->commit();
    }
    
    public function rollback() {
        $this->getConnection()->rollBack();
    }
    
    public function inTransaction() {
        $this->getConnection()->inTransaction();
    }
    
    /**
     * @return Connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * @return DBQueries
     */
    public function getQueryProvider() {
        return $this->queryProvider;
    }
    
    /*
     * inicio das implementacoes
     */
    
    /*
     * auth
     */
    
    /**
     * Retorna se usuario tem ou nao acesso ao modulo
     * Retorno:
     *         1 = Usuario Invalido
     *         2 = Modulo Invalido
     *         3 = Acesso Negado
     *         4 = Usuario invalido
     *        -1 = Acesso Liberado (Ok)
     *
     * @param String $login
     * @param String $pass
     * @param String $chave_mod
     * @return int
     */
    public function hasAcesso(Usuario $usuario, $chave_mod) {
        if (!$usuario) {
            return 1; // unknown user
        }
        $modulo = $this->getModuloByChave($chave_mod);
        if (!$modulo) {
            return 2; // unknown mod
        }
        if ($usuario->getStatus() == 0) {
            return 4; //inativo
        }
        return -1;        
    }

    public function hasAccessGlobal($id_usu, $id_mod) {
        $sql = $this->getQueryProvider()->hasAccessGlobal();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $id_usu, PDO::PARAM_INT);
        $statement->bindValue(':id_mod', $id_mod, PDO::PARAM_INT);
        $statement->execute();
        $ret = $statement->fetchAll();
        return sizeof($ret) > 0;
    }
    
    /**
     * Grava o ID da sessão atual, inserindo caso não exista, ou atualizando caso já exista.
     * @param $id_usu ID do usuario
     * @param $session_id ID da sessão a ser gravado, se não for especificado o ID da sessão atual é utilizado
     * @return void
     */
    public function salvarSessionId($id_usu, $session_id = null) {
        if ($session_id == null) {
            $session_id = session_id();
        }
        $sql = $this->getQueryProvider()->salvarSessionId();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $id_usu, PDO::PARAM_INT);
        $statement->bindValue(':session_id', $session_id, PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * Verifica se a sessão do usuário é valida, comparando o ID da sessão passado com o ID armazenado no banco
     * 
     * @param $id_usu ID do usuario
     * @param $session_id ID da sessão a ser comparado, se não especificado o ID da sessão atual é utilizado
     * @return bool TRUE somente se o ID da sessão existe no banco e é igual ao parametro, false caso contrario
     */
    public function verificarSessionId($id_usu, $session_id = null) {
        if ($session_id == null) {
            $session_id = session_id();
        }
        $sql = $this->getQueryProvider()->verificarSessionId();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $id_usu, PDO::PARAM_INT);
        $statement->bindValue(':session_id', $session_id, PDO::PARAM_STR);
        $statement->execute();
        $ret = $statement->fetchAll();
        // se nao existe session ativa para este usuario
        if (sizeof($ret) == 0) {
            return Session::SESSION_ENCERRADA;
        }
        // retornar status da session
        return $ret[0]['stat_session'];
    }

    /**
     * Atualiza o status da Session de um determinado usuário.
     *
     * @param int $id_usu O ID do usuário cuja session deve ser atualizada.
     * @param int $stat_session O novo status da Session
     */
    public function setSessionStatus($id_usu, $stat_session) {
        $sql = $this->getQueryProvider()->setSessionStatus();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $id_usu, PDO::PARAM_INT);
        $statement->bindValue(':stat_session', $stat_session, PDO::PARAM_INT);
        $statement->execute();
    }
    
    /**
     * Transforma o statement em um array de entidades, instanciadas a partir do 
     * metodo informado via parametro
     * @param type $statement
     * @param array $fn Metodo para instanciar a entidade (exemplo: array(ObjClass, 'methodName')
     * @return type
     */
    private function toList(Statement $statement, array $fn) {
        $statement->execute();
        $rs = $statement->fetchAll();
        $list = array();
        $method = new ReflectionMethod($fn[0], $fn[1]);
        foreach ($rs as $r) {
            $model = $method->invokeArgs(null, array($r));
            $list[] = $model;
        }
        return $list;
    }
    
    private function insertSeqModel(Statement $statement, SequencialModel $model, $lastId) {
        $r = $statement->execute();
        if ($r) {
            $model->setId((int) $this->lastInsertId($lastId[0], $lastId[1]));
        }
        return $r;
    }
    
    /*
     * getting - prioridades
     */
    
    public function getPrioridade($id) {
        $sql = $this->getQueryProvider()->selectPrioridade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $prioridades = $this->toList($statement, array('DB', 'createPrioridade'));
        return (sizeof($prioridades)) ? $prioridades[0] : null;
    }
    
    public function getPrioridades() {
        $sql = $this->getQueryProvider()->selectPrioridades();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createPrioridade'));
    }
    
    public function getPrioridadesByNomeOrDescricao($arg) {
        $sql = $this->getQueryProvider()->selectPrioridadesByNomeOrDescricao();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':arg', $arg, PDO::PARAM_STR);
        return $this->toList($statement, array('DB', 'createPrioridade'));
    }
    
    /*
     * getting - usuarios
     */
    
    public function getUsuario($id) {
        $sql = $this->getQueryProvider()->selectUsuario();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $usuarios = $this->toList($statement, array('DB', 'createUsuario'));
        return (sizeof($usuarios)) ? $usuarios[0] : null;
    }
    
    public function getUsuarios() {
        $sql = $this->getQueryProvider()->selectUsuarios();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createUsuario'));
    }
    
    public function getUsuarioByLogin($login) {
        $sql = $this->getQueryProvider()->selectUsuarioByLogin();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':login', $login, PDO::PARAM_STR);
        $usuarios = $this->toList($statement, array('DB', 'createUsuario'));
        return (sizeof($usuarios)) ? $usuarios[0]: null;
    }
    
    public function getUsuariosByLoginOrNome($arg) {
        $sql = $this->getQueryProvider()->selectUsuariosByLoginOrNome();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':arg', $arg, PDO::PARAM_STR);
        return $this->toList($statement, array('DB', 'createUsuario'));
    }
    
    /*
     * getting - grupos
     */
    
    public function getGrupo($id) {
        $sql = $this->getQueryProvider()->selectGrupo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $grupos = $this->toList($statement, array('DB', 'createGrupo'));
        return (sizeof($grupos)) ? $grupos[0] : null;
    }
    
    public function getGrupos() {
        $sql = $this->getQueryProvider()->selectGrupos();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createGrupo'));
    }
    
    public function getGruposByNomeOrDescricao($arg) {
        $sql = $this->getQueryProvider()->selectGruposByNomeOrDescricao();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':arg', $arg, PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createGrupo'));
    }

    public function getGrupoPaiByFilho(Grupo $filho) {
        $grupos = $this->getGruposPaiByFilho($filho);
        return (sizeof($grupos)) ? $grupos[0] : null;
    }

    public function getGruposPaiByFilho(Grupo $filho) {
        $sql = $this->getQueryProvider()->selectGruposPaiByFilho();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $filho->getId(), PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createGrupo'));
    }
    
    /*
     * getting - cargos
     */
    
    public function getCargo($id) {
        $sql = $this->getQueryProvider()->selectCargo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $cargos = $this->toList($statement, array('DB', 'createCargo'));
        return (sizeof($cargos)) ? $cargos[0] : null;
    }
    
    public function getCargos() {
        $sql = $this->getQueryProvider()->selectCargos();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createCargo'));
    }
    
    public function getCargosByNomeOrDescricao($arg) {
        $sql = $this->getQueryProvider()->selectCargosByNomeOrDescricao();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':arg', $arg, PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createCargo'));
    }

    public function getCargoPaiByFilho(Cargo $filho) {
        $cargos = $this->getCargosPaiByFilho($filho);
        return (sizeof($cargos)) ? $cargos[0] : null;
    }

    public function getCargosPaiByFilho(Cargo $filho) {
        $sql = $this->getQueryProvider()->selectCargosPaiByFilho();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $filho->getId(), PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createCargo'));
    }
    
    /*
     * getting - unidades
     */
    
    public function getUnidade($id) {
        $sql = $this->getQueryProvider()->selectUnidade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $unidades = $this->toList($statement, array('DB', 'createUnidade'));
        return (sizeof($unidades)) ? $unidades[0] : null;
    }
    
    /**
     * Retorna todas as unidades (Unidade) do Sistema
     * @return array
     */
    public function getUnidades() {
        $sql = $this->getQueryProvider()->selectUnidades();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createUnidade'));
    }
    
    /**
     * Retorna a Unidade especificada pelo codigo
     * @param int $cod_uni
     * @return Unidade
     */
    public function getUnidadesByCodigoOrNome($arg) {
        $sql = $this->getQueryProvider()->selectUnidadesByCodigoOrNome();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':arg', $arg, PDO::PARAM_STR);
        return $this->toList($statement, array('DB', 'createUnidade'));
    }

    public function getUnidadesByUsuario(Usuario $usuario) {
        $sql = $this->getQueryProvider()->selectUnidadesByUsuario();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $usuario->getId(), PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createUnidade'));
    }
    
    public function getUnidadesByGrupo($id_grupo) {
        $id_mod = SGA::getContext()->getModule()->getId();
        $id_usu = SGA::getContext()->getUser()->getId();
        $sql = $this->getQueryProvider()->selectUnidadesByGrupo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $id_usu, PDO::PARAM_INT);
        $statement->bindValue(':id_mod', $id_mod, PDO::PARAM_INT);
        $statement->bindValue(':id_grupo_1', $id_grupo, PDO::PARAM_INT);
        $statement->bindValue(':id_grupo_2', $id_grupo, PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createUnidade'));
    }
    
    /*
     * getting - modulos
     */
    
    /**
     * Retorna o modulo pelo id
     * @param int $id
     * @return Modulo
     */
    public function getModulo($id) {
        $sql = $this->getQueryProvider()->selectModulo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $modulos = $this->toList($statement, array('DB', 'createModulo'));
        return (sizeof($modulos)) ? $modulos[0] : null;
    }

    /**
     * Retorna os modulos do sistema, a partir do seu status
     * @param int $status
     * @return array
     */
    public function getModulos($status = array(Modulo::MODULO_ATIVO, Modulo::MODULO_INATIVO), $tipos = array(Modulo::MODULO_GLOBAL, Modulo::MODULO_UNIDADE)) {
        // garantindo somente numeros
        $status = Arrays::valuesToInt($status);
        $tipos = Arrays::valuesToInt($tipos);
        $sql = $this->getQueryProvider()->selectModulos();
        $sql = str_replace(':status', implode(",", $status), $sql);
        $sql = str_replace(':tipos', implode(",", $tipos), $sql);
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createModulo'));
    }
    
    public function getModulosUnidade() {
        return $this->getModulos(array(Modulo::MODULO_ATIVO), array(Modulo::MODULO_UNIDADE));
    }
    
    public function getModulosGlobal() {
        return $this->getModulos(array(Modulo::MODULO_ATIVO), array(Modulo::MODULO_GLOBAL));
    }

    /**
     * Retorna o modulo do sistema especificado pela chave e o status
     * @param String $chave
     * @param int $status
     * @return Modulo
     */
    public function getModuloByChave($chave, $status = Modulo::MODULO_ATIVO) {
        $sql = $this->getQueryProvider()->selectModuloByChave();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':chave', $chave, PDO::PARAM_STR);
        $statement->bindValue(':status', $status, PDO::PARAM_INT);
        $modulos = $this->toList($statement, array('DB', 'createModulo'));
        return (sizeof($modulos)) ? $modulos[0] : null;
    }
    
    /*
     * getting - servicos
     */
    
    public function getServico($id) {
        $sql = $this->getQueryProvider()->selectServico();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $servicos = $this->toList($statement, array('DB', 'createServico'));
        return (sizeof($servicos)) ? $servicos[0] : null;
    }
    
    public function getServicos() {
        $sql = $this->getQueryProvider()->selectServicos();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createServico'));
    }
    
    public function getServicosByNomeOrDescricao($arg) {
        $sql = $this->getQueryProvider()->selectServicosByNomeOrDescricao();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':arg', $arg, PDO::PARAM_STR);
        return $this->toList($statement, array('DB', 'createServico'));
    }
    
    public function getServicosMestre() {
        $sql = $this->getQueryProvider()->selectServicosMestre();
        $statement = $this->getConnection()->prepare($sql);
        return $this->toList($statement, array('DB', 'createServico'));
    }
    
    public function getServicosUnidade(Unidade $unidade) {
        $sql = $this->getQueryProvider()->selectServicosUnidade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_uni', $unidade->getId(), PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createServicoUnidade'));
    }
    
    /*
     * getting - atendimentos
     */
    
    public function getAtendimentosByServicoUnidade(ServicoUnidade $servico) {
        $sql = $this->getQueryProvider()->selectAtendimentosByServicoUnidade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_serv', $servico->getServico()->getId(), PDO::PARAM_INT);
        $statement->bindValue(':id_uni', $servico->getUnidade()->getId(), PDO::PARAM_INT);
        return $this->toList($statement, array('DB', 'createAtendimento'));
    }
    
    /*
     * inserting
     */
    
    public function insertGrupo(Grupo $grupo) {
        $sql = $this->getQueryProvider()->insertGrupo();
        $statement = $this->getConnection()->prepare($sql);
        if (!$grupo->isRoot()) {
            $statement->bindValue(':id_pai', $grupo->getParent()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_pai', null, PDO::PARAM_NULL);
        }
        $statement->bindValue(':nome', $grupo->getNome(), PDO::PARAM_INT);
        $statement->bindValue(':descricao', $grupo->getDescricao(), PDO::PARAM_INT);
        $rs = $statement->execute();
        if ($rs) {
            $grupo->setId((int) $this->lastInsertId('grupos_aninhados', 'id_grupo'));
        }
        return $rs;
    }
    
    public function insertCargo(Cargo $cargo) {
        $sql = $this->getQueryProvider()->insertCargo();
        $statement = $this->getConnection()->prepare($sql);
        if (!$cargo->isRoot()) {
            $statement->bindValue(':id_pai', $cargo->getParent()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_pai', null, PDO::PARAM_NULL);
        }
        $statement->bindValue(':nome', $cargo->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $cargo->getDescricao(), PDO::PARAM_STR);
        return $this->insertSeqModel($statement, $cargo, array('cargos_aninhados', 'id_cargo'));
    }
    
    public function insertUnidade(Unidade $unidade) {
        $sql = $this->getQueryProvider()->insertUnidade();
        $statement = $this->getConnection()->prepare($sql);
        if ($unidade->getGrupo()) {
            $statement->bindValue(':id_grupo', $unidade->getGrupo()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_grupo', null, PDO::PARAM_NULL);
        }
        $statement->bindValue(':cod_uni', $unidade->getCodigo(), PDO::PARAM_STR);
        $statement->bindValue(':nm_uni', $unidade->getNome(), PDO::PARAM_STR);
        return $this->insertSeqModel($statement, $unidade, array('unidades', 'id_uni'));
    }
    
    public function insertPrioridade(Prioridade $prioridade) {
        $sql = $this->getQueryProvider()->insertPrioridade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':nome', $prioridade->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $prioridade->getDescricao(), PDO::PARAM_STR);
        $statement->bindValue(':peso', $prioridade->getPeso(), PDO::PARAM_INT);
        return $this->insertSeqModel($statement, $prioridade, array('prioridades', 'id_pri'));
    }
    
    public function insertUsuario(Usuario $usuario, $senha) {
        $pass = Security::passEncode($senha);
        $sql = $this->getQueryProvider()->insertUsuario();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':login_usu', $usuario->getLogin(), PDO::PARAM_STR);
        $statement->bindValue(':nm_usu', $usuario->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':ult_nm_usu', $usuario->getSobrenome(), PDO::PARAM_STR);
        $statement->bindValue(':senha_usu', $pass, PDO::PARAM_STR);
        $r = $this->insertSeqModel($statement, $usuario, array('usuarios', 'id_usu'));
        if ($r) {
            $usuario->setSenha($pass);
        }
        return $r;
    }
    
    public function insertLotacao($id_usu, $id_grupo, $id_cargo) {
        $sql = $this->getQueryProvider()->insertLotacao();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $id_usu, PDO::PARAM_INT);
        $statement->bindValue(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $statement->bindValue(':id_cargo', $id_cargo, PDO::PARAM_INT);
        $statement->execute();
    }
    
    public function insertPermissaoModulo(PermissaoModulo $permissao) {
        $sql = $this->getQueryProvider()->insertPermissaoModuloCargo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_cargo', $permissao->getModulo()->getId(), PDO::PARAM_INT);
        $statement->bindValue(':id_mod', $permissao->getModulo()->getId(), PDO::PARAM_INT);
        $statement->bindValue(':permissao', 1, PDO::PARAM_INT);
        $statement->execute();
    }
    
    public function insertServico(Servico $servico) {
        $sql = $this->getQueryProvider()->insertServico();
        $statement = $this->getConnection()->prepare($sql);
        if ($servico->getMestre()) {
            $statement->bindValue(':id_macro', $servico->getMestre()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_macro', null, PDO::PARAM_NULL);
        }
        $statement->bindValue(':nome', $servico->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $servico->getDescricao(), PDO::PARAM_STR);
        $statement->bindValue(':status', $servico->getStatus(), PDO::PARAM_INT);
        return $this->insertSeqModel($statement, $servico, array('servicos', 'id_serv'));
    }
    
    public function insertServicoUnidade(Servico $servico, Unidade $unidade) {
        $sql = $this->getQueryProvider()->insertServicoUnidade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_uni', $unidade->getId(), PDO::PARAM_INT);
        $statement->bindValue(':id_serv', $servico->getId(), PDO::PARAM_INT);
        $statement->bindValue(':id_loc', 0, PDO::PARAM_INT);
        $statement->bindValue(':nome', $servico->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':sigla', $servico->getSigla(), PDO::PARAM_STR);
        $statement->bindValue(':status', $servico->getStatus(), PDO::PARAM_INT);    
        return $statement->execute();
    }
    
    /*
     * updating
     */
    
    public function updateGrupo(Grupo $grupo) {
        $sql = $this->getQueryProvider()->updateGrupo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $grupo->getId(), PDO::PARAM_INT);
        $statement->bindValue(':nome', $grupo->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $grupo->getDescricao(), PDO::PARAM_STR);
        if (!$grupo->isRoot()) {
            $statement->bindValue(':id_pai', $grupo->getParent()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_pai', null, PDO::PARAM_NULL);
        }
        return $statement->execute();
    }
    
    public function updateCargo(Cargo $cargo) {
        $sql = $this->getQueryProvider()->updateCargo();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $cargo->getId(), PDO::PARAM_INT);
        $statement->bindValue(':nome', $cargo->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $cargo->getDescricao(), PDO::PARAM_STR);
        if (!$cargo->isRoot()) {
            $statement->bindValue(':id_pai', $cargo->getParent()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_pai', null, PDO::PARAM_NULL);
        }
        return $statement->execute();
    }
    
    public function updateUnidade(Unidade $unidade) {
        $sql = $this->getQueryProvider()->updateUnidade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $unidade->getId(), PDO::PARAM_INT);
        if ($unidade->getGrupo()) {
            $statement->bindValue(':id_grupo', $unidade->getGrupo()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_grupo', null, PDO::PARAM_NULL);
        }
        $statement->bindValue(':nome', $unidade->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':codigo', $unidade->getCodigo(), PDO::PARAM_STR);
        return $statement->execute();
    }
    
    public function updatePrioridade(Prioridade $prioridade) {
        $sql = $this->getQueryProvider()->updatePrioridade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $prioridade->getId(), PDO::PARAM_INT);
        $statement->bindValue(':nome', $prioridade->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $prioridade->getDescricao(), PDO::PARAM_STR);
        $statement->bindValue(':peso', $prioridade->getPeso(), PDO::PARAM_INT);
        $statement->bindValue(':status', $prioridade->getStatus(), PDO::PARAM_INT);
        return $statement->execute();
    }
    
    public function updateUsuario(Usuario $user) {
        $sql = $this->getQueryProvider()->updateUsuario();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $user->getId(), PDO::PARAM_INT);
        $statement->bindValue(':login', $user->getLogin(), PDO::PARAM_STR);
        $statement->bindValue(':nome', $user->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':sobrenome', $user->getSobrenome(), PDO::PARAM_STR);
        $statement->execute();
    }
    
    public function updateServico(Servico $servico) {
        $sql = $this->getQueryProvider()->updateServico();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $servico->getId(), PDO::PARAM_INT);
        if ($servico->getMestre()) {
            $statement->bindValue(':id_macro', $servico->getMestre()->getId(), PDO::PARAM_INT);
        } else {
            $statement->bindValue(':id_macro', null, PDO::PARAM_NULL);
        }
        $statement->bindValue(':nome', $servico->getNome(), PDO::PARAM_STR);
        $statement->bindValue(':descricao', $servico->getDescricao(), PDO::PARAM_STR);
        $statement->bindValue(':status', $servico->getStatus(), PDO::PARAM_STR);
        $statement->execute();
        $this->updateSubServicos($servico);
    }
    
    public function updateSubServicos(Servico $macro) {
        $sql = $this->getQueryProvider()->updateSubServicos();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_macro', $macro->getId(), PDO::PARAM_INT);
        $statement->bindValue(':status', $macro->getStatus(), PDO::PARAM_STR);
        $statement->execute();
    }
    
    public function updateServicoUnidade(Servico $servico, Unidade $unidade) {
        $sql = $this->getQueryProvider()->updateServicoUnidade();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id', $servico->getId(), PDO::PARAM_INT);
        $statement->bindValue(':sigla', $servico->getSigla(), PDO::PARAM_STR);
        $statement->bindValue(':status', $servico->getStatus(), PDO::PARAM_INT);
        $statement->execute();
    }
    
    public function updateLotacao(Lotacao $lotacao) {
        $sql = $this->getQueryProvider()->updateLotacao();
        $statement = $this->getConnection()->prepare($sql);
        $statement->bindValue(':id_usu', $lotacao->getUsuario()->getId(), PDO::PARAM_INT);
        $statement->bindValue(':id_grupo', $lotacao->getGrupo()->getId(), PDO::PARAM_INT);
        $statement->bindValue(':id_cargo', $lotacao->getCargo()->getId(), PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() === 1;
    }
        
}
