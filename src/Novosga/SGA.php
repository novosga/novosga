<?php
namespace Novosga;

use \Novosga\Business\AcessoBusiness;

/**
 * SGA Slim Framework App
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SGA extends \Slim\Slim {
    
    const VERSION = "1.1.2";
    const CHARSET = "utf-8";
    
    // SESSION KEYS
    const K_CURRENT_USER    = "SGA_CURRENT_USER";
    
    private $context;
    private $acessoBusiness;
    
    public function __construct(array $userSettings = array()) {
        $twig = new \Slim\Views\Twig();
        $userSettings = array_merge($userSettings, array(
            'view' => $twig
        ));
        if (!$userSettings['debug']) {
            $twig->parserOptions = array(
                'cache' => $userSettings['cache']
            );
        }
        parent::__construct($userSettings);
        
        $this->view()->set('version', SGA::VERSION);
        $this->view()->set('lang', \Novosga\Util\I18n::lang());
        
        $this->context = new SGAContext($this, $userSettings['db']);
        
        $this->view()->parserExtensions = array(
            new \Slim\Views\TwigExtension(),
            new \Twig_Extensions_Extension_I18n(),
            new \Twig_Extension_Debug()
        );

        $this->add(new \Novosga\Slim\InstallMiddleware($this->getContext()));
        $this->add(new \Novosga\Slim\AuthMiddleware($this->getContext()));
        
        $this->acessoBusiness = new AcessoBusiness();
    }
    
    /**
     * @return SGAContext
     */
    public function getContext() {
        return $this->context;
    }
    
    /**
     * @return AcessoBusiness
     */
    public function getAcessoBusiness() {
        return $this->acessoBusiness;
    }
        
    public function gotoLogin() {
        $this->redirect($this->request()->getRootUri() . '/login');
    }
    
    public function gotoHome() {
        $this->redirect($this->request()->getRootUri() . '/home');
    }
    
    public function gotoModule() {
        $this->redirect($this->request()->getRootUri() . '/modules/' . $this->getContext()->getModulo()->getChave());
    }
    
    /**
     * Autentica o usuario do SGA
     * @param type $user
     * @param type $pass
     * @return Usuario|null
     */
    public function auth($login, $pass) {
        $em = $this->getContext()->database()->createEntityManager();
        $config = \Novosga\Model\Configuracao::get($em, \Novosga\Auth\Authentication::KEY);
        $auth = ($config) ? $config->getValor() : array();
        $authMethods = \Novosga\Auth\AuthFactory::createList($this->getContext(), $auth);
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
        $lang = \Novosga\Util\I18n::locale();
        return current(explode('_', $lang));
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
