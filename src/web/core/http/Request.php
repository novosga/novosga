<?php
namespace core\http;

/**
 * Request Wrapper
 * @author rogeriolino
 */
class Request {
    
    public function __construct() {
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
