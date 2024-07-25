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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DeleteTrait
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
trait DeleteTrait
{
    #[Route('/{id}', methods: ['DELETE'])]
    public function doDelete(int $id): Response
    {
        $object = $this->getRepository()->find($id);

        return $this->remove($object);
    }
}
