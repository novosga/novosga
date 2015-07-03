<?php

namespace Novosga\Http;

/**
 * Session Wrapper.
 */
class Session
{
    public function __construct()
    {
        session_start();
    }

    private function key($key)
    {
        if (defined('MODULE')) {
            return $this->moduleKey($key);
        }

        return $this->globalKey($key);
    }

    private function moduleKey($key)
    {
        return MODULE.'_'.$key;
    }

    private function globalKey($key)
    {
        return 'GLOBAL_'.$key;
    }

    /**
     * Define ou adicionar um valor na sessao.
     *
     * @param String $key
     * @param mixed  $valor
     */
    public function set($key, $valor)
    {
        SessionCache::set($this->key($key), $valor);
    }

    /**
     * Define ou adicionar um valor na sessao Global.
     *
     * @param String $key
     * @param mixed  $valor
     */
    public function setGlobal($key, $valor)
    {
        SessionCache::set($this->globalKey($key), $valor);
    }

    /**
     * Retorna o valor da chave guardada na sessao.
     *
     * @param String $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return SessionCache::get($this->key($key));
    }

    public function getGlobal($key)
    {
        return SessionCache::get($this->globalKey($key));
    }

    /**
     * Retorna se a chave informada ja esta guardada na sessao.
     *
     * @param String $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return SessionCache::exists($this->key($key));
    }

    public function existsInGlobal($key)
    {
        return isset($_SESSION[$this->globalKey($key)]);
    }

    public function existsInModule($key)
    {
        return (defined('MODULE') && isset($_SESSION[$this->moduleKey($key)]));
    }

    /**
     * Remove da sessao a chave informada.
     *
     * @param String $key
     */
    public function del($key)
    {
        SessionCache::del($this->key($key));
    }

    /**
     * Remove todos valores armazenados, mas mantem a session viva.
     */
    public function clear()
    {
        SessionCache::clear();
    }

    /**
     * Destroi (encerra) a sessao, removendo
     * todos os valores guardados.
     */
    public function destroy()
    {
        $_SESSION = array();
        session_destroy();
    }
}

/**
 * SessionCache.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SessionCache
{
    private static $cache = array();

    public static function set($key, $value)
    {
        self::$cache[$key] = $value;
//        $_SESSION[$key] = serialize($value);
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        } elseif (isset($_SESSION[$key])) {
            self::$cache[$key] = $_SESSION[$key];

            return self::$cache[$key];
        }
    }

    public static function del($key)
    {
        unset(self::$cache[$key]);
        unset($_SESSION[$key]);
    }

    public static function exists($key)
    {
        return isset(self::$cache[$key]) || isset($_SESSION[$key]);
    }

    public static function clear()
    {
        $_SESSION = array();
        self::$cache = array();
    }
}
