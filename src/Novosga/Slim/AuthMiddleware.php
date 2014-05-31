<?php
namespace Novosga\Slim;

use Novosga\Model\Modulo;
use Novosga\Context;
use Slim\Middleware;

/**
 * SlimFramework middleware para verificar
 * se o usuario esta logado
 * 
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AuthMiddleware extends Middleware {
    
    private $context;
    public static $freePages = array('login', 'logout', 'api');
    
    public function __construct(Context $context) {
        $this->context = $context;
    }
    
    public function call() {
        if (NOVOSGA_INSTALLED) {
            $req = $this->app->request();
            $uri = substr($req->getResourceUri(), 1);
            if (strpos($uri, '/')) {
                $uri = substr($uri, 0, strpos($uri, '/'));
            }
            if (!in_array($uri, self::$freePages)) {
                $user = $this->context->getUser();
                $logged = $user != null;
                if (!$logged) {
                    $this->app->redirect($req->getRootUri() . '/login');
                }
                if ($user) {
                    // verifica se ha outra pessoa usando o mesmo usuario
                    if ($user->getSessionId() != session_id()) {
                        $this->app->redirect($req->getRootUri() . '/logout');
                    }
                    $unidade = $user->getUnidade();
                    $acessoBusiness = $this->context->app()->getAcessoBusiness();
                    // modulos globais
                    $this->app->view()->set('modulosGlobal', $acessoBusiness->modulos($this->context, $user, Modulo::MODULO_GLOBAL));
                    // modulos unidades
                    if ($unidade) {
                        $this->app->view()->set('modulosUnidade', $acessoBusiness->modulos($this->context, $user, Modulo::MODULO_UNIDADE));
                    }
                    $this->app->view()->set('unidades', $acessoBusiness->unidades($this->context, $user));
                    $this->app->view()->set('unidade', $unidade);
                    $this->app->view()->set('usuario', $user);
                }
            }
        }
        $this->next->call();
    }
    
}