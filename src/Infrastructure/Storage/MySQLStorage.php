<?php

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
use Novosga\Entity\Contador;
use Novosga\Entity\Servico;
use Novosga\Entity\Unidade;

/**
 * MySQL Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MySQLStorage extends RelationalStorage
{
    /**
     * {@inheritdoc}
     */
    protected function numeroAtual(Connection $conn, Unidade $unidade, Servico $servico): int
    {
        $contadorTable = $this->om->getClassMetadata(Contador::class)->getTableName();
     
        $rs = $conn->executeQuery("
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

        $numeroAtual = (int) $rs->fetchOne();
        
        return $numeroAtual;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function preAcumularAtendimentos(Connection $conn, Unidade $unidade = null)
    {
        $conn->exec('SET foreign_key_checks = 0');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function preApagarDadosAtendimento(Connection $conn, Unidade $unidade = null)
    {
        $conn->exec('SET foreign_key_checks = 0');
    }
}
