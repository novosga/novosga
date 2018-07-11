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
 * AtendimentosController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 * @Route("/api/atendimentos")
 */
class AtendimentosController extends ApiCrudController
{
    use Actions\GetTrait,
        Actions\FindTrait;
    
    public function getEntityName()
    {
        return \Novosga\Entity\Atendimento::class;
    }
}
