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

use App\Entity\ServicoUnidade;
use App\Repository\ServicoMetadataRepository;
use App\Repository\ServicoRepository;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Infrastructure\StorageInterface;
use Novosga\Service\ServicoServiceInterface;

/**
 * ServicoService.
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ServicoService implements ServicoServiceInterface
{
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly ServicoRepository $servicoRepository,
        private readonly ServicoMetadataRepository $servicoMetadataRepository,
    ) {
    }

    public function getById(int $id): ?ServicoInterface
    {
        return $this->servicoRepository->find($id);
    }

    /** {@inheritDoc} */
    public function meta(ServicoInterface $servico, string $name, mixed $value = null): ?EntityMetadataInterface
    {
        if ($value === null) {
            $metadata = $this->servicoMetadataRepository->get($servico, self::ATTR_NAMESPACE, $name);
        } else {
            $metadata = $this->servicoMetadataRepository->set($servico, self::ATTR_NAMESPACE, $name, $value);
        }

        return $metadata;
    }

    /** {@inheritDoc} */
    public function servicosUnidade(UnidadeInterface|int $unidade, array $where = []): array
    {
        $qb = $this->storage
            ->getManager()
            ->createQueryBuilder()
            ->select('e')
            ->from(ServicoUnidade::class, 'e')
            ->join('e.servico', 's')
            ->where('e.unidade = :unidade')
            ->setParameter('unidade', $unidade)
            ->andWhere('s.deletedAt IS NULL')
            ->orderBy('s.nome', 'ASC');

        foreach ($where as $k => $v) {
            if (is_array($v)) {
                $qb->andWhere("e.{$k} IN (:{$k})");
            } elseif (is_string($v)) {
                $qb->andWhere("e.{$k} LIKE :{$k}");
            } else {
                $qb->andWhere("e.{$k} = :{$k}");
            }
            $qb->setParameter($k, $v);
        }

        $servicos = $qb
            ->getQuery()
            ->getResult();

        return $servicos;
    }

    /** {@inheritDoc} */
    public function servicosIndisponiveis(UnidadeInterface|int $unidade, UsuarioInterface|int $usuario): array
    {
        return $this->storage
            ->getManager()
            ->createQuery("
                SELECT
                    e
                FROM
                    App\Entity\ServicoUnidade e
                    JOIN e.servico s
                WHERE
                    s.deletedAt IS NULL AND
                    e.ativo = TRUE AND
                    e.unidade = :unidade AND
                    s.id NOT IN (
                        SELECT s2.id
                        FROM App\Entity\ServicoUsuario a
                        JOIN a.servico s2
                        WHERE a.usuario = :usuario AND a.unidade = :unidade
                    )
            ")
            ->setParameter('usuario', $usuario)
            ->setParameter('unidade', $unidade)
            ->getResult();
    }

    /** {@inheritDoc} */
    public function gerarSigla(int $sequencia): string
    {
        if ($sequencia <= 0) {
            return '';
        }

        $letter = '';
        while ($sequencia != 0) {
            $p = ($sequencia - 1) % 26;
            $letter = chr(65 + $p) . $letter;
            $sequencia = intval(($sequencia - $p) / 26);
        }

        return $letter;
    }
}
