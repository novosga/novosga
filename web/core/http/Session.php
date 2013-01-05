<?php
namespace core\http;


/**
 * Session Wrapper
 *
 */
class Session {
    
    /** Sessão encerrada: Usuário deslogado. */
    const SESSION_ENCERRADA = 0;

    /** Sessão ativa: Usuário logado. */
    const SESSION_ATIVA = 1;

    /**
     * Sessão com dados fora de sincronia com o Banco de Dados,
     * o próximo acesso a qualquer página do sistema irá efetuar
     * uma recarga transparente da Sessão e retornar ao status SESSION_ATIVA.
     */
    const SESSION_DESATUALIZADA = 2;

    public function __construct() {
        session_start();
    }
    
    private function key($key) {
        if (defined('MODULE')) {
            return $this->moduleKey($key);
        }
        return $this->globalKey($key);
    }
    
    private function moduleKey($key) {
        return MODULE . '_' . $key;
    }
    
    private function globalKey($key) {
        return 'GLOBAL_' . $key;
    }
    
    /**
     * Define ou adicionar um valor na sessao
     * @param String $key
     * @param mixed $valor
     */
    public function set($key, $valor) {
        SessionCache::set($this->key($key), $valor);
    }
    
    /**
     * Define ou adicionar um valor na sessao Global
     * @param String $key
     * @param mixed $valor
     */
    public function setGlobal($key, $valor) {
        SessionCache::set($this->globalKey($key), $valor);
    }
    
    /**
     * Retorna o valor da chave guardada na sessao
     * @param String $key
     * @return mixed
     */
    public function get($key) {
        return SessionCache::get($this->key($key));
    }
    
    public function getGlobal($key) {
        return SessionCache::get($this->globalKey($key));
    }
    
    /**
     * Retorna se a chave informada ja esta guardada na sessao
     * @param String $key
     * @return bool
     */
    public function exists($key) {
        return SessionCache::exists($this->key($key));
    }
    
    public function existsInGlobal($key) {
        return isset($_SESSION[$this->globalKey($key)]);
    }
    
    public function existsInModule($key) {
        return (defined('MODULE') && isset($_SESSION[$this->moduleKey($key)]));
    }

    /**
     * Remove da sessao a chave informada.
     * @param String $key
     */
    public function del($key) {
        SessionCache::del($this->key($key));
    }

    /**
     * Marca os dados da Session do usuário especificado como desatualizados.<br>
     * Os dados da Session serão recarregados de forma transparente ao usuário.<br>
     * Este método deve ser invocado quando houver alguma alteração no usuário que faça os dados
     * armazenados na session saírem de sincronia com os dados do Banco de Dados.<br>
     * <br>
     * Caso a session não exista(o usuário especificado não esteja logado) esse método não tem efeito.<br>
     *
     * @param int $id_usu O ID do usuario da Session a ser invalidada.
     */
    public static function invalidate($id_usu) {
        DB::getAdapter()->set_session_status($id_usu, Session::SESSION_DESATUALIZADA);
    }

    /**
     * Remove todos valores armazenados, mas mantem a session viva
     */
    public function clear() {
        SessionCache::clear();
    }
    
    /**
     * Destroi (encerra) a sessao, removendo 
     * todos os valores guardados
     *
     */
    public function destroy() {
        $_SESSION = array();
        session_destroy();
    }

}

/**
 * SessionCache
 * 
 * @author rogeriolino
 */
class SessionCache {
    
    private static $cache = array();
    
    public static function set($key, $value) {
        self::$cache[$key] = $value;
//        $_SESSION[$key] = serialize($value);
        $_SESSION[$key] = $value;
    }
    
    public static function get($key) {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        } else if (isset($_SESSION[$key])) {
            self::$cache[$key] = $_SESSION[$key];
            return self::$cache[$key];
        }
    }
    
    public static function del($key) {
        unset(self::$cache[$key]);
        unset($_SESSION[$key]);
    }
    
    public static function exists($key) {
        return isset(self::$cache[$key]) || isset($_SESSION[$key]);
    }
    
    public static function clear() {
        $_SESSION = array();
        self::$cache = array();
    }
    
}