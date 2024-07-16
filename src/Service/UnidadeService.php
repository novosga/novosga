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

use App\Entity\Contador;
use App\Entity\ServicoUnidade;
use App\Entity\Unidade;
use App\Entity\UnidadeMeta;
use App\Infrastructure\StorageInterface;
use App\Repository\ContadorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\ServicoUnidadeInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Service\UnidadeServiceInterface;

/**
 * UnidadeService.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class UnidadeService implements UnidadeServiceInterface
{
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly EntityManagerInterface $em,
        private readonly ContadorRepository $contadorRepository,
    ) {
    }

    /** {@inheritDoc} */
    public function meta(UnidadeInterface $unidade, string $name, mixed $value = null): EntityMetadataInterface
    {
        $repo = $this->storage->getRepository(UnidadeMeta::class);

        if ($value === null) {
            $metadata = $repo->get($unidade, $name);
        } else {
            $metadata = $repo->set($unidade, $name, $value);
        }

        return $metadata;
    }

    public function addServicoUnidade(ServicoInterface $servico, UnidadeInterface $unidade, string $sigla): ServicoUnidadeInterface
    {
        $su = new ServicoUnidade();
        $su
            ->setUnidade($unidade)
            ->setServico($servico)
            ->setIncremento(1)
            ->setMensagem('')
            ->setNumeroInicial(1)
            ->setPeso(1)
            ->setTipo(ServicoUnidadeInterface::ATENDIMENTO_TODOS)
            ->setSigla($sigla)
            ->setAtivo(false);

        $contador = $this->contadorRepository->findOneBy([
            'unidade' => $unidade,
            'servico' => $servico,
        ]);

        if (!$contador) {
            $contador = (new Contador())
                ->setServico($servico)
                ->setUnidade($unidade);
        }

        $contador->setNumero($su->getNumeroInicial());

        $this->em->persist($contador);
        $this->em->persist($su);
        $this->em->flush();

        return $su;
    }
}
