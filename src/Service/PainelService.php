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

namespace App\Service;

use App\Entity\Painel;
use App\Repository\PainelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\PainelInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Service\PainelServiceInterface;
use Symfony\Component\Uid\Uuid;

/**
 * PainelService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class PainelService implements PainelServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PainelRepository $repository,
    ) {
    }

    public function getById(int $id): ?PainelInterface
    {
        return $this->repository->find($id);
    }

    public function getByPublicId(string $publicId): ?PainelInterface
    {
        return $this->repository->findOneBy([
            'publicId' => $publicId,
        ]);
    }

    /** @return PainelInterface[] */
    public function findByUnidade(UnidadeInterface $unidade): array
    {
        return $this->repository->findBy(
            ['unidade' => $unidade->getId()],
            ['nome' => 'ASC'],
        );
    }

    public function build(): PainelInterface
    {
        return new Painel();
    }

    public function save(PainelInterface $painel): PainelInterface
    {
        if (!$painel->getPublicId()) {
            $painel->setPublicId(Uuid::v7()->toString());
        }

        $this->em->persist($painel);
        $this->em->flush();

        return $painel;
    }

    public function remove(PainelInterface $painel): void
    {
        $this->em->remove($painel);
        $this->em->flush();
    }
}
