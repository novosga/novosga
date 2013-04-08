<?php
namespace core;

/**
 * Security
 *
 * @author rogeriolino
 */
class Security {

    public static function passEncode($pass) {
        return self::hash($pass);
    }

/**
 * Verifica se o hash passado como segundo argumento representa
 * o mesmo valor que o hash produzido com o primeiro argumento.
 *
 * @param string $pass Senha
 * @param string $stored A versão em hash armazenada da senha
 *
 * @return boolean
 */
    public static function passCheck($pass, $stored) {
        return (self::hash($pass, $stored) == $stored);
    }

/**
 * Gera um hash seguro utilizando 'salt' randômico ou fornecido
 * e o algorítimo Blowfish.
 *
 * @param  string $text Valor que será utilizado de entrada
 * @param  string $salt Um salt que pode ser utilizado para gerar um hash previamente
 * armazenado.
 *
 * @return string Hash da entrada
 */
    public static function hash($text, $salt = null) {
        if ($salt === null) {
            $randomSalt = substr(str_replace(array('+', '='), '.', base64_encode(sha1(uniqid(rand(), true), true))), 0, 22);
            $salt = vsprintf('$2y$%02d$%s', array(10, $randomSalt));
        }

        return crypt($text, $salt);
    }
}
