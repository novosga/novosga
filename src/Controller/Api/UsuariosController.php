<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Symfony\Component\Routing\Annotation\Route;

/**
 * UsuariosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 * @Route("/api/usuarios")
 */
class UsuariosController extends ApiCrudController
{
    use Actions\GetTrait,
        Actions\FindTrait;
    
    public function getEntityName()
    {
        return \Novosga\Entity\Usuario::class;
    }
}
