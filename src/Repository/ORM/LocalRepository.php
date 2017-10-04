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
use Novosga\Repository\LocalRepositoryInterface;

/**
 * LocalRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class LocalRepository extends EntityRepository implements LocalRepositoryInterface
{
}
