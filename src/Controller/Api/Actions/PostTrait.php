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

namespace App\Controller\Api\Actions;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PostTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait PostTrait
{
    #[Route('', methods: ['POST'])]
    public function doPost(Request $request): Response
    {
        $json = $request->getContent();
        $object = $this->deserialize($json);

        return $this->add($object);
    }
}
