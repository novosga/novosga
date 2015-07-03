<?php

namespace Novosga\Http;

use Novosga\Util\Arrays;

/**
 * Cookie Wrapper.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Cookie
{
    /**
     * Define ou adicionar um valor no cookie.
     *
     * @param String $key
     * @param mixed  $valor
     */
    public function set($key, $value)
    {
        $_COOKIE[$key] = $value;
        $expire = time() + 60 * 60 * 24 * 30;
        setcookie($key, $value, $expire);
    }

    /**
     * Retorna o valor da chave guardada no cookie.
     *
     * @param String $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return Arrays::value($_COOKIE, $key);
    }

    /**
     * Retorna se a chave informada ja esta guardada no cookie.
     *
     * @param String $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * Remove da sessao a chave informada.
     *
     * @param String $key
     */
    public function del($key)
    {
        unset($_COOKIE[$key]);
    }

    /**
     * Remove todos valores armazenados.
     */
    public function clear()
    {
        $_COOKIE = array();
    }
}
