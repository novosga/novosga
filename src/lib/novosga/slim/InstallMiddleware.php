<?php
namespace novosga\slim;

/**
 * SlimFramework middleware para verificar
 * se o Novo SGA está instalado
 * 
 * @author rogeriolino
 */
class InstallMiddleware extends \Slim\Middleware {
    
    public function __construct() {
    }
    
    public function call() {
        $req = $this->app->request();
        $res = $this->app->response();
        $uri = $req->getResourceUri();
        if (!\novosga\Config::SGA_INSTALLED && !self::isInstallPage($uri)) {
            $this->app->redirect($req->getRootUri() . '/install');
        } else if (\novosga\Config::SGA_INSTALLED && self::isInstallPage($uri)) {
            $this->app->redirect($req->getRootUri() . '/login');
        } else {
            $this->next->call();
        }
    }
    
    public static function isInstallPage($uri) {
        return substr($uri, 0, 8) === '/install';
    }
    
}