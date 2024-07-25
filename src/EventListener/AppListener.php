<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;

/**
 * AppListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AppListener
{
    protected function isApiRequest(Request $request): bool
    {
        $output = [];
        $path   = $request->getPathInfo();
        preg_match("/^\/(api|\w+\.\w+\/api)/", $path, $output);

        $match = count($output) > 0;

        return $match;
    }

    protected function isAdminRequest(Request $request): bool
    {
        $path  = $request->getPathInfo();
        $match = strpos($path, '/admin') === 0;

        return $match;
    }

    protected function isHomeRequest(Request $request): bool
    {
        $path  = $request->getPathInfo();
        $match = $path === '/';

        return $match;
    }

    protected function isLoginRequest(Request $request): bool
    {
        $path  = $request->getPathInfo();
        $match = $path === '/login';

        return $match;
    }

    protected function isPingRequest(Request $request): bool
    {
        $path  = $request->getPathInfo();
        $match = $path === '/ping';

        return $match;
    }

    /**
     * Returns the module name or false
     * @param Request $request
     * @return mixed
     */
    protected function isModuleRequest(Request $request)
    {
        $path  = $request->getPathInfo();
        preg_match("/\/(\w+\.\w+)\/?(.*)/", $path, $match);

        if (count($match) > 2) {
            return $match[1];
        }

        return false;
    }
}
