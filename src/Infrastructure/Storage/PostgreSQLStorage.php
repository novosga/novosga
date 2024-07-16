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

namespace App\Infrastructure\Storage;

use Doctrine\DBAL\Connection;
use App\Entity\Contador;
use App\Entity\Servico;
use App\Entity\Unidade;

/**
 * PostgreSQL Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PostgreSQLStorage extends RelationalStorage
{
    /**
     * {@inheritdoc}
     */
    protected function numeroAtual(Connection $conn, Unidade $unidade, Servico $servico): int
    {
        $contadorTable = $this->em->getClassMetadata(Contador::class)->getTableName();

        $stmt = $conn->executeQuery("
            SELECT numero 
            FROM {$contadorTable} 
            WHERE
                unidade_id = :unidade AND
                servico_id = :servico
            FOR UPDATE
        ", [
            'unidade' => $unidade->getId(),
            'servico' => $servico->getId(),
        ]);

        $numeroAtual = (int) $stmt->fetchOne();

        return $numeroAtual;
    }

    /**
     * {@inheritdoc}
     */
    protected function preAcumularAtendimentos(Connection $conn, Unidade $unidade = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function preApagarDadosAtendimento(Connection $conn, Unidade $unidade = null)
    {
    }
}
