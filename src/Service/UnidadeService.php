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
use App\Repository\ContadorRepository;
use App\Repository\UnidadeMetadataRepository;
use App\Repository\UnidadeRepository;
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
        private readonly EntityManagerInterface $em,
        private readonly UnidadeRepository $unidadeRepository,
        private readonly ContadorRepository $contadorRepository,
        private readonly UnidadeMetadataRepository $unidadeMetadataRepository,
    ) {
    }

    public function getById(int $id): ?UnidadeInterface
    {
        return $this->unidadeRepository->find($id);
    }

    /** {@inheritDoc} */
    public function meta(UnidadeInterface $unidade, string $name, mixed $value = null): ?EntityMetadataInterface
    {
        if ($value === null) {
            $metadata = $this->unidadeMetadataRepository->get($unidade, self::ATTR_NAMESPACE, $name);
        } else {
            $metadata = $this->unidadeMetadataRepository->set($unidade, self::ATTR_NAMESPACE, $name, $value);
        }

        return $metadata;
    }

    public function addServicoUnidade(
        ServicoInterface $servico,
        UnidadeInterface $unidade,
        string $sigla
    ): ServicoUnidadeInterface {
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
