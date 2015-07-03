<?php

namespace Novosga\Twig;

/**
 * SecFormat Twig filter.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class SecFormat extends \Twig_SimpleFilter
{
    public function __construct()
    {
        parent::__construct('secFormat', function (\Twig_Environment $env, $string) {
            if (strpos($string, ':')) {
                return $string;
            }
            $seconds = (int) $string;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds - ($hours * 3600)) / 60);

            return str_pad($hours, 2, '0', STR_PAD_LEFT).':'.
                    str_pad($minutes, 2, '0', STR_PAD_LEFT).':'.
                    str_pad($seconds % 60, 2, '0', STR_PAD_LEFT);
        }, array('needs_environment' => true));
    }
}
