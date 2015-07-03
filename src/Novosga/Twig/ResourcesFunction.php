<?php

namespace Novosga\Twig;

use Slim\Slim;

/**
 * Resources Twig function.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ResourcesFunction extends \Twig_SimpleFunction
{
    public function __construct()
    {
        parent::__construct('resources', function (\Twig_Environment $env, $param1, $param2 = '', $version = null) {

            $req = Slim::getInstance()->request();
            $baseUrl = $req->getUrl().$req->getRootUri();

            if (!empty($param2)) {
                $url = "$baseUrl/modules/$param2/resources/$param1";
            } else {
                $url = "$baseUrl/$param1";
            }
            if (!$version) {
                $version = \Novosga\App::VERSION;
            }

            return "$url?v=$version";

        }, array('needs_environment' => true));
    }
}
