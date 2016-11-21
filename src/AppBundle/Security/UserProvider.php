<?php

namespace AppBundle\Security;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;

/**
 * UserProvider
 *
 * @author rogerio
 */
class UserProvider extends EntityUserProvider
{
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, 'Novosga\Entity\Usuario', 'login');
    }
    
}
