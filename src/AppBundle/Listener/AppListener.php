<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\Request;

/**
 * AppListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class AppListener
{
    protected function isApiRequest(Request $request)
    {
        $path = $request->getPathInfo();
        $isApi = strpos($path, '/api') === 0;
        
        return $isApi;
    }
}
