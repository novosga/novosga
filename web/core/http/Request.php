<?php
namespace core\http;

/**
 * Request Wrapper
 *
 */
class Request {
    
    public function __construct() {
        // Workaround para Magic Quotes Enabled
        // Magic Quotes esta depreciado a partir do PHP 5.3.0 e será removido no PHP 6
        // mas ainda vem ativado por padrão no 5.2.x
        if (get_magic_quotes_gpc()) {
            function stripslashes_deep($value) {
                $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
                return $value;
            }
            $_POST = array_map('stripslashes_deep', $_POST);
            $_GET = array_map('stripslashes_deep', $_GET);
            $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
            $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        }
    }
    
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
    
    public function getParameter($name) {
        if (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        return null;
    }
    
    public function setParameter($name, $value) {
        $_REQUEST[$name] = $value;
    }
    
    public function getCookie($name) {
        return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : null;
    }
    
    public function setCookie($name, $value) {
        $_COOKIE[$name] = $value;
    }

}
