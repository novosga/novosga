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

use App\Entity\ServicoMeta;
use App\Entity\ServicoUnidade;
use App\Infrastructure\StorageInterface;
use App\Repository\ServicoRepository;
use Novosga\Entity\EntityMetadataInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
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
    ) {
    }

    public function getById(int $id): ?ServicoInterface
    {
        return $this->servicoRepository->find($id);
    }

    /** @return ServicoMeta */
    public function meta(ServicoInterface $servico, $name, $value = null): EntityMetadataInterface
    {
        $repo = $this->storage->getRepository(ServicoMeta::class);
        
        if ($value === null) {
            $metadata = $repo->get($servico, $name);
        } else {
            $metadata = $repo->set($servico, $name, $value);
        }
        
        return $metadata;
    }

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
            } else if (is_string($v)) {
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

    public function gerarSigla(int $sequencia): string
    {
        if ($sequencia <= 0) {
            return '';
        }

        $letter = '';        
        while ($sequencia != 0) {
           $p = ($sequencia - 1) % 26;
           $c = intval(($sequencia - $p) / 26);
           $letter = chr(65 + $p) . $letter;
        }

        return $letter;
    }
}
