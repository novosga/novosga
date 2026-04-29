<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Entity\Cliente;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends ApiCrudController<Cliente>
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
#[Route('/api/clientes')]
class ClientesController extends ApiCrudController
{
    use Actions\GetTrait;
    use Actions\FindTrait;
    use Actions\PostTrait;
    use Actions\PutTrait;
    use Actions\DeleteTrait;

    public function getEntityName(): string
    {
        return Cliente::class;
    }

    public function getSearchableFields(): array
    {
        return ['id', 'nome', 'documento', 'email', 'telefone'];
    }
}
