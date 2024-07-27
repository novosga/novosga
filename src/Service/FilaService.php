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

use Doctrine\ORM\QueryBuilder;
use App\Entity\Atendimento;
use App\Entity\ServicoUnidade;
use App\Entity\ServicoUsuario;
use Doctrine\ORM\EntityManagerInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
use Novosga\Event\QueueOrderingEvent;
use Novosga\Service\FilaServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * FilaService
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class FilaService implements FilaServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /** {@inheritDoc} */
    public function getFilaAtendimento(
        UnidadeInterface $unidade,
        UsuarioInterface $usuario,
        array $servicosUsuario = [],
        string $tipoFila = self::TIPO_TODOS,
        int $maxResults = 0,
    ): array {
        $ids = [];
        foreach ($servicosUsuario as $servico) {
            if ($servico->getUsuario()->getId() === $usuario->getId()) {
                $ids[]   = $servico->getServico()->getId();
            }
        }

        if (empty($ids)) {
            return [];
        }

        $builder = $this
            ->builder()
            ->join(
                ServicoUsuario::class,
                'servicoUsuario',
                'WITH',
                'servicoUsuario.servico = servico AND servicoUsuario.usuario = :usuario'
            )
            ->andWhere('(atendimento.usuario IS NULL OR atendimento.usuario = :usuario)')
            ->andWhere('atendimento.status = :status')
            ->andWhere('atendimento.unidade = :unidade')
            ->andWhere('servico.id IN (:servicos)')
            ->setParameter('status', AtendimentoService::SENHA_EMITIDA)
            ->setParameter('unidade', $unidade)
            ->setParameter('usuario', $usuario)
            ->setParameter('servicos', $ids);

        // se nao atende todos, filtra pelo tipo de atendimento
        switch ($tipoFila) {
            case self::TIPO_NORMAL:
            case self::TIPO_PRIORIDADE:
                $s = ($tipoFila === self::TIPO_NORMAL) ? '=' : '>';
                $where = "prioridade.peso $s 0";
                $builder->andWhere($where);
                break;
            case self::TIPO_AGENDAMENTO:
                $builder->andWhere("atendimento.dataAgendamento IS NOT NULL");
                break;
        }

        $query = $this
            ->applyOrders($builder, $unidade, $usuario)
            ->getQuery();

        if ($maxResults > 0) {
            $query->setMaxResults($maxResults);
        }

        return $query->getResult();
    }

    /** {@inheritDoc} */
    public function getFilaServico(UnidadeInterface $unidade, ServicoInterface $servico): array
    {
        $builder = $this
            ->builder()
            ->where('atendimento.status = :status')
            ->andWhere('atendimento.unidade = :unidade')
            ->andWhere('atendimento.servico = :servico')
            ->setParameter('status', AtendimentoService::SENHA_EMITIDA)
            ->setParameter('unidade', $unidade)
            ->setParameter('servico', $servico);

        $rs = $this
            ->applyOrders($builder, $unidade)
            ->getQuery()
            ->getResult();

        return $rs;
    }

    /** {@inheritDoc} */
    public function getFilaUnidade(UnidadeInterface $unidade): array
    {
        $builder = $this
            ->builder()
            ->where('atendimento.status = :status')
            ->andWhere('atendimento.unidade = :unidade')
            ->setParameter('status', AtendimentoService::SENHA_EMITIDA)
            ->setParameter('unidade', $unidade);

        $rs = $this
            ->applyOrders($builder, $unidade)
            ->getQuery()
            ->getResult();

        return $rs;
    }

    private function builder(): QueryBuilder
    {
        $qb = $this
            ->em
            ->createQueryBuilder()
            ->select([
                'atendimento',
                'prioridade',
                'unidade',
                'servico',
            ])
            ->from(Atendimento::class, 'atendimento')
            ->join('atendimento.prioridade', 'prioridade')
            ->join('atendimento.unidade', 'unidade')
            ->join('atendimento.servico', 'servico')
            ->join(
                ServicoUnidade::class,
                'servicoUnidade',
                'WITH',
                'servicoUnidade.unidade = unidade AND servicoUnidade.servico = servico'
            );

        return $qb;
    }

    /** Aplica a ordenação na QueryBuilder */
    private function applyOrders(
        QueryBuilder $builder,
        UnidadeInterface $unidade,
        UsuarioInterface $usuario = null,
    ): QueryBuilder {
        $this
            ->dispatcher
            ->dispatch(new QueueOrderingEvent($unidade, $usuario, $builder));

        return $builder;
    }
}
