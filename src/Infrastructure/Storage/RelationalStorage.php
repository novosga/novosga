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

use Doctrine\DBAL\LockMode;
use Exception;
use Novosga\Entity\Atendimento;

/**
 * ORM Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class RelationalStorage extends DoctrineStorage
{
    /**
     * {@inheritdoc}
     */
    public function chamar(Atendimento $atendimento)
    {
        $this->om->getConnection()->beginTransaction();

        try {
            $this->om->lock($atendimento, LockMode::PESSIMISTIC_WRITE);
            $this->om->merge($atendimento);
            $this->om->getConnection()->commit();
            $this->om->flush();
        } catch (Exception $e) {
            $this->om->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function encerrar(Atendimento $atendimento, array $codificados, Atendimento $novoAtendimento = null)
    {
        $this->om->beginTransaction();
        
        try {
            foreach ($codificados as $codificado) {
                $this->om->persist($codificado);
            }
            
            if ($novoAtendimento) {
                $this->om->persist($novoAtendimento);
            }
            
            $this->om->merge($atendimento);
            $this->om->commit();
            $this->om->flush();
        } catch (Exception $e) {
            try {
                $this->om->rollback();
            } catch (Exception $ex) {
            }
            throw new $e;
        }
    }
}
