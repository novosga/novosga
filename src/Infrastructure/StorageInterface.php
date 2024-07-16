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

namespace App\Infrastructure;

use App\Entity\Agendamento;
use App\Entity\Atendimento;
use App\Entity\Unidade;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * StorageInterface
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
interface StorageInterface
{
    public function getManager(): EntityManagerInterface;
 
    public function getRepository(string $className): EntityRepository;
    
    /**
     * Gera uma nova senha de atendimento
     */
    public function distribui(Atendimento $atendimento, Agendamento $agendamento = null): void;

    public function chamar(Atendimento $atendimento): void;

    /**
     * @param Atendimento $atendimento
     * @param array       $codificados
     * @param Atendimento $novoAtendimento
     */
    public function encerrar(Atendimento $atendimento, array $codificados, Atendimento $novoAtendimento = null): void;

    /**
     * Move os dados de atendimento para o histórico
     */
    public function acumularAtendimentos(?Unidade $unidade, array $ctx = []);

    /**
     * Apaga todos os dados de atendimentos
     */
    public function apagarDadosAtendimento(?Unidade $unidade, array $ctx = []);
}
