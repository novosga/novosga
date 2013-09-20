<?php
namespace novosga\slim;

use \novosga\SGAContext;
use \novosga\business\AcessoBusiness;

/**
 * SlimFramework middleware para verificar
 * se o usuario esta logado
 * 
 * @author rogeriolino
 */
class AuthMiddleware extends \Slim\Middleware {
    
    private $context;
    
    public function __construct(SGAContext $context) {
        $this->context = $context;
    }
    
    public function call() {
        $req = $this->app->request();
        $res = $this->app->response();
        $uri = $req->getResourceUri();
        $user = $this->context->getUser();
        if (\novosga\Config::SGA_INSTALLED && $uri !== "/login") {
            $logged = $user != null;
            if (!$logged) {
                $this->app->redirect($req->getRootUri() . '/login');
            }
        }
        if ($user) {
            $unidade = $user->getUnidade();
            // modulos globais
            $this->app->view()->assign('modulosGlobal', AcessoBusiness::modulos($user, \novosga\model\Modulo::MODULO_GLOBAL));
            // modulos unidades
            if ($unidade) {
                $this->app->view()->assign('modulosUnidade', AcessoBusiness::modulos($user, \novosga\model\Modulo::MODULO_UNIDADE));
            }
            $this->app->view()->assign('unidade', $unidade);
            $this->app->view()->assign('usuario', $user);
            $this->app->view()->setData('usuario', $this->context->getUser());
        }
        $this->next->call();
    }
    
}