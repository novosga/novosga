<?php

namespace Novosga\Slim;

use Novosga\App;
use Novosga\Context;

/**
 * SlimFramework middleware para verificar
 * se o Novo SGA está instalado.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class InstallMiddleware extends \Slim\Middleware
{
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function call()
    {
        $req = $this->app->request();
        $uri = $req->getResourceUri();
        $installed = App::isInstalled();
        if (!$installed && !self::isInstallPage($uri)) {
            $this->app->response()->redirect($this->app->urlFor('install'));
        } elseif ($installed && self::isInstallPage($uri)) {
            $this->app->response()->redirect($this->app->urlFor('login'));
        } else {
            $this->next->call();
        }
    }

    public static function isInstallPage($uri)
    {
        return substr($uri, 0, 8) === '/install';
    }
}
