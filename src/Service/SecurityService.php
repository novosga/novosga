<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

/**
 * SecurityService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SecurityService
{
    /**
     * @var string
     */
    private $rootDir;
    
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }
    
    /**
     * Returns websocket server secret
     * @return string
     */
    public function getWebsocketSecret(): string
    {
        $filename = "{$this->rootDir}/var/websocket";
        if (!file_exists($filename)) {
            $secret = uniqid('novosga_websocket_');
            file_put_contents($filename, $secret);
        } else {
            $secret = file_get_contents($filename);
        }
        
        return $secret;
    }
}
