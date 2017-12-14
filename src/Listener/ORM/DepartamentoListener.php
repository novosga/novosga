<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Listener\ORM;

use Exception;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Novosga\Entity\Departamento;

/**
 * DepartamentoListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class DepartamentoListener
{
    public function prePersist(Departamento $departamento, LifecycleEventArgs $args)
    {
        $departamento->setCreatedAt(new \DateTime);
    }

    public function preUpdate(Departamento $departamento, LifecycleEventArgs $args)
    {
        $departamento->setUpdatedAt(new \DateTime);
    }
}
