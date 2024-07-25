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

namespace App\Controller\Api;

use App\Entity\Prioridade;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @extends ApiCrudController<Prioridade>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 */
#[Route('/api/prioridades')]
class PrioridadesController extends ApiCrudController
{
    use Actions\GetTrait;
    use Actions\FindTrait;
    use Actions\PostTrait;
    use Actions\PutTrait;
    use Actions\DeleteTrait;

    public function getEntityName(): string
    {
        return Prioridade::class;
    }
}
