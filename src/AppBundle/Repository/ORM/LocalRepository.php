<?php

namespace AppBundle\Repository\ORM;

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
