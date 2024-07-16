<?php

declare(strict_types=1);

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Configuration;

use Doctrine\ORM\QueryBuilder;
use App\Entity\Unidade;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;

/**
 * OrderingParameter
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class OrderingParameter implements ParameterInterface
{
    private ?Unidade $unidade = null;
    private ?Usuario $usuario = null;
    private ?EntityManagerInterface $em = null;
    private ?QueryBuilder $queryBuilder = null;
    
    public function getUnidade(): Unidade
    {
        return $this->unidade;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setUnidade(Unidade $unidade): static
    {
        $this->unidade = $unidade;

        return $this;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function setEntityManager(EntityManagerInterface $em): static
    {
        $this->em = $em;

        return $this;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): static
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }
}
