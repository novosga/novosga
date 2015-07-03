<?php

namespace Novosga\Slim;

use Novosga\App;
use Novosga\Model\Modulo;
use Novosga\Context;
use Slim\Middleware;

/**
 * SlimFramework middleware para verificar
 * se o usuario esta logado.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AuthMiddleware extends Middleware
{
    private $context;
    public static $freePages = array('login', 'logout', 'api', 'print');

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function call()
    {
        if (App::isInstalled()) {
            $req = $this->app->request();
            $uri = substr($req->getResourceUri(), 1);
            if (strpos($uri, '/')) {
                $uri = substr($uri, 0, strpos($uri, '/'));
            }
            if (!in_array($uri, self::$freePages)) {
                $user = $this->context->getUser();
                if ($user) {
                    // verifica se ha outra pessoa usando o mesmo usuario
                    if ($user->getSessionId() != session_id()) {
                        if ($this->app->request->isAjax()) {
                            // se for ajax devolve o json informando sobre a sessao invalida
                            $response = new \Novosga\Http\JsonResponse();
                            $response->invalid = true;
                            echo $response->toJson();
                            exit();
                        }
                        $this->app->response()->redirect($this->app->urlFor('logout'));
                    } else {
                        $unidade = $user->getUnidade();
                        $acessoService = $this->context->app()->getAcessoService();
                        // modulos globais
                        $this->app->view()->set('modulosGlobal', $acessoService->modulos($this->context, $user, Modulo::MODULO_GLOBAL));
                        // modulos unidades
                        if ($unidade) {
                            $this->app->view()->set('modulosUnidade', $acessoService->modulos($this->context, $user, Modulo::MODULO_UNIDADE));
                        }
                        $this->app->view()->set('unidades', $acessoService->unidades($this->context, $user));
                        $this->app->view()->set('unidade', $unidade);
                        $this->app->view()->set('usuario', $user);
                    }
                } else {
                    if ($this->app->request->isAjax()) {
                        // se for ajax devolve o json informando sobre a sessao inativa
                        $response = new \Novosga\Http\JsonResponse();
                        $response->inactive = true;
                        echo $response->toJson();
                        exit();
                    }
                    $this->app->response()->redirect($this->app->urlFor('login'));
                }
            }
        }
        $this->next->call();
    }
}
