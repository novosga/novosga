<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        }, ['needs_environment' => true]);
    }
}
