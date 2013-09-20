<?php
namespace novosga;

/**
 * SGA Base class and request handler
 * 
 * contem alguns metodos do sistema
 */
class SGA extends \Slim\Slim {
    
    const VERSION = "1.0.0";
    const CHARSET = "utf-8";
    
    // SESSION KEYS
    const K_CURRENT_USER    = "SGA_CURRENT_USER";
    
    private $context;
    
    public function __construct(array $userSettings = array()) {
        parent::__construct($userSettings);
        $this->context = new SGAContext();
    }
    
    /**
     * Returns SGAContext
     * @return SGAContext
     */
    public function getContext() {
        return $this->context;
    }
    
    public function gotoLogin() {
        $this->redirect($this->request()->getRootUri() . '/login');
    }
    
    public function gotoHome() {
        $this->redirect($this->request()->getRootUri() . '/home');
    }
    
    /**
     * Autentica o usuario do SGA
     * @param type $user
     * @param type $pass
     * @return Usuario|null
     */
    public static function auth($login, $pass) {
        $config = \novosga\model\Configuracao::get(\novosga\auth\Authentication::KEY);
        $auth = ($config) ? $config->getValor() : array();
        $authMethods = \novosga\auth\AuthFactory::createList($auth);
        foreach ($authMethods as $auth) {
            try {
                $user = $auth->auth($login, $pass);
                if ($user) {
                    return $user;
                }
            } catch (\Exception $e) {
            }
        }
        return false;
    }
    
    public static function defaultClientLanguage() {
        $lang = \novosga\util\I18n::locale();
        return current(explode('_', $lang));
    }
    
    public static function url() {
        if (func_num_args() == 0) {
            return $_SERVER['REQUEST_URI'];
        } else {
            $arg = func_get_arg(0);
            if (!is_array($arg)) {
                if ($arg[0] == '/') {
                    return '?' . substr($arg, 1);
                }
                $arg = array(SGA::K_PAGE => $arg);
            }
            $url = '';
            $module = \novosga\util\Arrays::value($arg, SGA::K_MODULE);
            if (empty($module) && defined('MODULE')) {
                $module = MODULE;
            }
            if (!empty($module)) {
                $url .= SGA::K_MODULE . '=' . $module;
                $page = \novosga\util\Arrays::value($arg, SGA::K_PAGE);
                if (!empty($page)) {
                    $url .= '&' . SGA::K_PAGE . '=' . $page;
                }
            }
            return (!empty($url)) ? "?$url" : self::url();
        }
    }

    /**
     * Retorna informacoes gerais sobre o sistema
     */
    public static function info() {
        ob_start();
        phpinfo();
        $info = ob_get_contents();
        ob_end_clean();
        return $info;
    }    

}

//spl_autoload_register(array('novosga\SGA', 'load'));
//set_exception_handler(array('novosga\SGA', 'onException'));
//set_error_handler(array('novosga\SGA', 'onError'));
