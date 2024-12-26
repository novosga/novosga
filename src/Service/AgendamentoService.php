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

namespace App\Service;

use App\Entity\Agendamento;
use App\Entity\Cliente;
use App\Repository\AgendamentoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Service\AgendamentoServiceInterface;

/**
 * AgendamentoService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AgendamentoService implements AgendamentoServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AgendamentoRepository $repository,
    ) {
    }

    public function getById(int $id): ?AgendamentoInterface
    {
        return $this->repository->find($id);
    }

    public function build(): AgendamentoInterface
    {
        $agendamento = new Agendamento();
        $agendamento->setCliente(new Cliente());

        return $agendamento;
    }

    public function save(AgendamentoInterface $agendamento): AgendamentoInterface
    {
        $this->em->persist($agendamento);
        $this->em->flush();

        return $agendamento;
    }
}
