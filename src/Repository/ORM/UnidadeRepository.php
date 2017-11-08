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
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Novosga\Entity\Lotacao;
use Novosga\Repository\UnidadeRepositoryInterface;

/**
 * UnidadeRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeRepository extends EntityRepository implements UnidadeRepositoryInterface
{
    use SoftDeleteTrait;
    
    /**
     * Retorna todas as unidades ordenadas por nome
     *
     * @return Unidade[]
     */
    public function findAll()
    {
        return $this->findBy([], ['nome' => 'ASC']);
    }
    
    /**
     *
     * @param Usuario $usuario
     * @return Unidade[]
     */
    public function findByUsuario(Usuario $usuario)
    {
        $qb = $this
                ->createQueryBuilder('e')
                ->where('e.deletedAt IS NULL');
                
        if (!$usuario->isAdmin()) {
            $qb
                ->join(Lotacao::class, 'l', 'WITH', 'l.unidade = e')
                ->where('l.usuario = :usuario')
                ->setParameter('usuario', $usuario);
        }
                        
        $unidades = $qb
                ->getQuery()
                ->getResult();
        
        return $unidades;
    }
}
