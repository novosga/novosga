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

use App\Entity\Cliente;
use App\Repository\ClienteMetadataRepository;
use App\Repository\ClienteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\ClienteInterface;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Service\ClienteServiceInterface;

class ClienteService implements ClienteServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClienteRepository $clienteRepository,
        private readonly ClienteMetadataRepository $clienteMetadataRepository,
    ) {
    }

    public function getById(int $id): ?ClienteInterface
    {
        return $this->clienteRepository->find($id);
    }

    public function build(): ClienteInterface
    {
        return new Cliente();
    }

    public function save(ClienteInterface $cliente): ClienteInterface
    {
        $this->em->persist($cliente);
        $this->em->flush();

        return $cliente;
    }

    /** {@inheritDoc} */
    public function meta(ClienteInterface $cliente, string $name, mixed $value = null): ?EntityMetadataInterface
    {
        if ($value === null) {
            $metadata = $this->clienteMetadataRepository->get($cliente, self::ATTR_NAMESPACE, $name);
        } else {
            $metadata = $this->clienteMetadataRepository->set($cliente, self::ATTR_NAMESPACE, $name, $value);
        }

        return $metadata;
    }
}
