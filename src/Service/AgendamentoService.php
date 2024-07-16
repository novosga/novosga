<?php

namespace App\Service;

use App\Entity\Agendamento;
use App\Entity\Cliente;
use App\Repository\AgendamentoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Service\AgendamentoServiceInterface;

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
