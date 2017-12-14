<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Repository\DepartamentoRepositoryInterface;

/**
 * DepartamentoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class DepartamentoRepository extends EntityRepository implements DepartamentoRepositoryInterface
{
}
