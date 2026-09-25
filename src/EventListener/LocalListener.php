<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use Exception;
use App\Entity\Atendimento;
use App\Entity\Local;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;

/**
 * LocalListener
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsEntityListener]
class LocalListener
{
    public function preRemove(Local $local, PreRemoveEventArgs $args): void
    {
        /** @var EntityManagerInterface */
        $em = $args->getObjectManager();

        // ServicoUnidade nao tem (e nao tem mais, desde a v2.x) nenhum
        // vinculo com Local -- essa checagem e' resquicio da v1.x. Quem
        // realmente referencia Local hoje e' o Atendimento. Bloqueia a
        // remocao se existir qualquer atendimento (historico incluido)
        // vinculado a esse local.
        $total = (int) $em
            ->createQueryBuilder()
            ->select('COUNT(1)')
            ->from(Atendimento::class, 'e')
            ->where('e.local = :local')
            ->setParameter('local', $local)
            ->getQuery()
            ->getSingleScalarResult();

        if ($total > 0) {
            throw new Exception('Não é possível remover o local porque ele está vinculado a atendimentos existentes.');
        }
    }
}
