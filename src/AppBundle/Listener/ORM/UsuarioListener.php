<?php

namespace AppBundle\Listener\ORM;

use Exception;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Novosga\Entity\Usuario;
use Novosga\Entity\Atendimento;

/**
 * UsuarioListener
 *
 * @author rogerio
 */
class UsuarioListener
{
    
    public function preRemove(Usuario $usuario, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        
        $total = (int) $em->createQueryBuilder()
            ->select('COUNT(e)')
            ->from(Atendimento::class, 'e')
            ->where('e.usuario = :usuario OR e.usuarioTriagem = :usuario')
            ->setParameter('usuario', $usuario)
            ->getQuery()
            ->getSingleScalarResult();
        
        if ($total > 0) {
            throw new Exception('Não é possível remover o usuário porque já existe atendimento vinculado.');
        }
    }
}
