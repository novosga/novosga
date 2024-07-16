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

use App\Entity\Lotacao;
use App\Repository\LotacaoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\LotacaoInterface;
use Novosga\Service\LotacaoServiceInterface;

class LotacaoService implements LotacaoServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LotacaoRepository $lotacaoRepository,
    ) {
    }

    public function getById(int $id): ?LotacaoInterface
    {
        return $this->lotacaoRepository->find($id);
    }

    public function build(): LotacaoInterface
    {
        return new Lotacao();
    }

    public function save(LotacaoInterface $Lotacao): LotacaoInterface
    {
        $this->em->persist($Lotacao);
        $this->em->flush();

        return $Lotacao;
    }
}
