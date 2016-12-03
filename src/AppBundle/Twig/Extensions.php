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

/**
 * Resources Twig filter.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class Extensions extends \Twig_Extension
{
    public function getName()
    {
        return 'novosga';
    }

    public function getFunctions()
    {
        return [
            new ResourcesFunction(),
        ];
    }

    public function getFilters()
    {
        return [
            new SecFormat(),
        ];
    }
}
