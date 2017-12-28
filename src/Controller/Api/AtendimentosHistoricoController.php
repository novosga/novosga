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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * AtendimentosHistoricoController
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 *
 * @Route("/api/atendimentoshistorico")
 */
class AtendimentosHistoricoController extends ApiCrudController
{
    
    use Actions\GetTrait,
        Actions\FindTrait;
    
    public function __construct($rootDir)
    {
        parent::__construct(\Novosga\Entity\AtendimentoHistorico::class, $rootDir);
    }
}
