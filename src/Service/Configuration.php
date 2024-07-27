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

namespace App\Service;

use Novosga\Service\ConfigurationInterface;

/**
 * Configuration
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /** @var array<string,mixed> */
    private array $default = [];
    /** @var array<string,mixed> */
    private array $custom = [];

    public function __construct(string $rootDir)
    {
        $this->default = require("{$rootDir}/config/app.default.php");

        $customFile = "{$rootDir}/config/app.php";
        if (file_exists($customFile)) {
            $this->custom = require($customFile);
        }
    }

    /** @return array<string,mixed> */
    public function get(string $key): mixed
    {
        $value = null;
        $obj = $this->default;
        $tokens = explode('.', $key);

        foreach ($tokens as $prop) {
            if (is_array($obj)) {
                $value = $this->resolve($prop, $obj);
                $obj = $value;
            } else {
                break;
            }
        }

        return $value;
    }

    /** @param array<string,mixed> $obj */
    private function resolve(string $key, array $obj): mixed
    {
        return $this->resolveValue($key, $this->custom, $obj);
    }

    /**
     * @param array<string,mixed> $primary
     * @param array<string,mixed> $secondary
     */
    private function resolveValue(string $key, array $primary, array $secondary): mixed
    {
        if (isset($primary[$key])) {
            return $primary[$key];
        }

        if (isset($secondary[$key])) {
            return $secondary[$key];
        }

        return null;
    }
}
