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

namespace App\Infrastructure\Storage;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\LockMode;
use Exception;
use App\Entity\Agendamento;
use App\Entity\Atendimento;
use App\Entity\AtendimentoCodificado;
use App\Entity\AtendimentoCodificadoHistorico;
use App\Entity\AtendimentoHistorico;
use App\Entity\AtendimentoHistoricoMeta;
use App\Entity\AtendimentoMeta;
use App\Entity\Contador;
use App\Entity\PainelSenha;
use App\Entity\ServicoUnidade;
use App\Repository\ServicoUnidadeRepository;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use PDO;

/**
 * ORM Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class RelationalStorage extends DoctrineStorage
{
    abstract protected function numeroAtual(
        Connection $conn,
        UnidadeInterface $unidade,
        ServicoInterface $servico
    ): int;

    abstract protected function preAcumularAtendimentos(Connection $conn, ?UnidadeInterface $unidade = null): void;

    abstract protected function preApagarDadosAtendimento(Connection $conn, ?UnidadeInterface $unidade = null): void;

    /**
     * Reinicia os contadores dos serviços da unidade
     */
    protected function reiniciarContadores(Connection $conn, int $unidadeId): void
    {
        $contadorTable = $this->em->getClassMetadata(Contador::class)->getTableName();
        $servicoUnidadeTable = $this->em->getClassMetadata(ServicoUnidade::class)->getTableName();

        $query = $conn->prepare("
            UPDATE {$contadorTable}
            SET numero = (
                SELECT su.numero_inicial
                FROM {$servicoUnidadeTable} su
                WHERE
                    su.unidade_id = {$contadorTable}.unidade_id AND
                    su.servico_id = {$contadorTable}.servico_id
            )
            WHERE (unidade_id = :unidade OR :unidade = 0)
        ");
        $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
        $query->execute();
    }

    /** {@inheritdoc} */
    public function chamar(AtendimentoInterface $atendimento): void
    {
        $this->em->getConnection()->beginTransaction();

        try {
            $this->em->lock($atendimento, LockMode::PESSIMISTIC_WRITE);
            $this->em->persist($atendimento);
            $this->em->getConnection()->commit();
            $this->em->flush();
        } catch (Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }

    /** {@inheritdoc} */
    public function encerrar(
        AtendimentoInterface $atendimento,
        array $codificados,
        AtendimentoInterface $novoAtendimento = null
    ): void {
        $this->em->beginTransaction();

        try {
            foreach ($codificados as $codificado) {
                $this->em->persist($codificado);
            }

            if ($novoAtendimento) {
                $this->em->persist($novoAtendimento);
            }

            $this->em->persist($atendimento);
            $this->em->commit();
            $this->em->flush();
        } catch (Exception $e) {
            try {
                $this->em->rollback();
            } catch (Exception $ex) {
            }
            throw $e;
        }
    }

    /** {@inheritdoc} */
    public function distribui(AtendimentoInterface $atendimento, ?AgendamentoInterface $agendamento = null): void
    {
        $self = $this;
        /** @var Connection */
        $conn = $this->em->getConnection();

        $conn->transactional(function (Connection $conn) use ($self, $atendimento, $agendamento) {
            $contadorTable = $this->em->getClassMetadata(Contador::class)->getTableName();
            $unidade = $atendimento->getUnidade();
            $servico = $atendimento->getServico();

            /** @var ServicoUnidadeRepository */
            $servicoUnidadeRepository = $this->getRepository(ServicoUnidade::class);
            $su = $servicoUnidadeRepository->get($unidade, $servico);

            $numeroAtual = $self->numeroAtual($conn, $unidade, $servico);
            $numeroSenha = $numeroAtual;

            $numeroSenha += $su->getIncremento();
            if ($su->getNumeroFinal() > 0 && $numeroSenha > $su->getNumeroFinal()) {
                $numeroSenha = $su->getNumeroInicial();
            }

            $rs = $conn->executeQuery("
                UPDATE {$contadorTable}
                SET numero = :numero
                WHERE
                    unidade_id = :unidade AND
                    servico_id = :servico AND
                    numero = :numeroAtual
            ", [
                'numero' => $numeroSenha,
                'unidade' => $unidade->getId(),
                'servico' => $servico->getId(),
                'numeroAtual' => $numeroAtual,
            ]);
            $success = $rs->rowCount() === 1;

            if (!$success) {
                throw new Exception('Error updating ticket counter');
            }

            $atendimento->setDataChegada(new DateTime());
            $atendimento->getSenha()->setNumero($numeroAtual);

            if ($agendamento) {
                $agendamento
                    ->setSituacao(Agendamento::SITUACAO_CONFIRMADO)
                    ->setDataConfirmacao(new DateTime());
            }

            $this->em->persist($atendimento);
            $this->em->flush();
        });
    }

    /** {@inheritdoc} */
    public function acumularAtendimentos(?UnidadeInterface $unidade, array $ctx = []): void
    {
        $self = $this;
        $conn = $this->em->getConnection();

        $conn->transactional(function (Connection $conn) use ($self, $unidade, $ctx) {
            $data      = new DateTime();
            $unidadeId = $unidade ? $unidade->getId() : 0;

            if (isset($ctx['data']) && $ctx['data'] instanceof DateTime) {
                $data = $ctx['data'];
            }

            $data = $data->format('Y-m-d H:i:s');

            // tables name
            $historicoTable        = $this->em->getClassMetadata(AtendimentoHistorico::class)->getTableName();
            $historicoCodifTable   = $this->em->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
            $historicoMetaTable    = $this->em->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
            $atendimentoTable      = $this->em->getClassMetadata(Atendimento::class)->getTableName();
            $atendimentoCodifTable = $this->em->getClassMetadata(AtendimentoCodificado::class)->getTableName();
            $atendimentoMetaTable  = $this->em->getClassMetadata(AtendimentoMeta::class)->getTableName();
            $painelSenhaTable      = $this->em->getClassMetadata(PainelSenha::class)->getTableName();

            $helper = new \App\Helper\DoctrineHelper($this->em);

            // columns
            $historicoColumns       = $helper->getEntityColumns(AtendimentoHistorico::class);
            $historicoMetaColumns   = $helper->getEntityColumns(AtendimentoHistoricoMeta::class);
            $historicoCodifColumns  = $helper->getEntityColumns(AtendimentoCodificadoHistorico::class);

            $self->preAcumularAtendimentos($conn, $unidade);

            // copia os atendimentos para o historico
            $sql = "
                INSERT INTO {$historicoTable}
                (
                    " . join(', ', $historicoColumns) . "
                )
                SELECT
                    a." . join(', a.', $historicoColumns) . "
                FROM
                    {$atendimentoTable} a
                WHERE
                    a.dt_cheg <= :data AND (a.unidade_id = :unidade OR :unidade = 0)
            ";

            // atendimentos pais (nao oriundos de redirecionamento)
            $query = $conn->prepare("$sql AND a.atendimento_id IS NULL");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // atendimentos filhos (oriundos de redirecionamento)
            $query = $conn->prepare("{$sql} AND a.atendimento_id IS NOT NULL");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // copia os metadados
            $sql = "
                INSERT INTO $historicoMetaTable
                (
                    " . join(', ', $historicoMetaColumns) . "
                )
                SELECT
                    a." . join(', a.', $historicoMetaColumns) . "
                FROM
                    {$atendimentoMetaTable} a
                WHERE
                    atendimento_id IN (
                        SELECT b.id
                        FROM {$atendimentoTable} b
                        WHERE
                            b.dt_cheg <= :data AND
                            (b.unidade_id = :unidade OR :unidade = 0)
                    )
            ";
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // copia os atendimentos codificados para o historico
            $query = $conn->prepare("
                INSERT INTO $historicoCodifTable
                (
                    " . join(', ', $historicoCodifColumns) . "
                )
                SELECT
                    ac." . join(', ac.', $historicoCodifColumns) . "
                FROM
                    {$atendimentoCodifTable} ac
                    JOIN {$atendimentoTable} a ON a.id = ac.atendimento_id
                WHERE
                    a.dt_cheg <= :data AND
                    (a.unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // limpa atendimentos codificados
            $query = $conn->prepare("
                DELETE FROM {$atendimentoCodifTable}
                WHERE atendimento_id IN (
                    SELECT id
                    FROM {$atendimentoTable}
                    WHERE
                        dt_cheg <= :data AND
                        (unidade_id = :unidade OR :unidade = 0)
                )
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // limpa metadata
            $query = $conn->prepare("
                DELETE FROM {$atendimentoMetaTable}
                WHERE atendimento_id IN (
                    SELECT id
                    FROM {$atendimentoTable}
                    WHERE
                        dt_cheg <= :data AND
                        (unidade_id = :unidade OR :unidade = 0)
                )
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // limpa o auto-relacionamento para poder excluir os atendimento sem dar erro de constraint (#136)
            $query = $conn->prepare("
                DELETE FROM {$atendimentoTable}
                WHERE
                    atendimento_id IS NOT NULL AND
                    dt_cheg <= :data AND
                    (unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // limpa atendimentos da unidade
            $query = $conn->prepare("
                DELETE FROM {$atendimentoTable}
                WHERE
                    dt_cheg <= :data AND
                    (unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // limpa a tabela de senhas a serem exibidas no painel
            $query = $conn->prepare("
                DELETE FROM {$painelSenhaTable}
                WHERE (unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            $rs = $conn->executeQuery("SELECT COUNT(*) FROM {$atendimentoTable}");
            $total = (int) $rs->fetchOne();

            // reinicia o contador das senhas
            if ($total === 0) {
                $this->reiniciarContadores($conn, $unidadeId);
            }
        });
    }

    /** {@inheritdoc} */
    public function apagarDadosAtendimento(?UnidadeInterface $unidade, array $ctx = []): void
    {
        $self = $this;
        $conn = $this->em->getConnection();

        $conn->transactional(function ($conn) use ($self, $unidade) {
            // tables name
            $historicoTable        = $this->em->getClassMetadata(AtendimentoHistorico::class)->getTableName();
            $historicoCodifTable   = $this->em->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
            $historicoMetaTable    = $this->em->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
            $atendimentoTable      = $this->em->getClassMetadata(Atendimento::class)->getTableName();
            $atendimentoCodifTable = $this->em->getClassMetadata(AtendimentoCodificado::class)->getTableName();
            $atendimentoMetaTable  = $this->em->getClassMetadata(AtendimentoMeta::class)->getTableName();

            $unidadeId = $unidade ? $unidade->getId() : 0;

            $self->preApagarDadosAtendimento($conn, $unidade);

            $query = $conn->prepare("
                DELETE FROM {$historicoCodifTable}
                WHERE atendimento_id IN (SELECT id FROM {$historicoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            $query = $conn->prepare("
                DELETE FROM {$historicoMetaTable}
                WHERE atendimento_id IN (SELECT id FROM {$historicoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            $query = $conn->prepare("
                DELETE FROM {$historicoTable}
                WHERE unidade_id = :unidade OR :unidade = 0
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            $query = $conn->prepare("
                DELETE FROM {$atendimentoCodifTable}
                WHERE atendimento_id IN (SELECT id FROM {$atendimentoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            $query = $conn->prepare("
                DELETE FROM {$atendimentoMetaTable}
                WHERE atendimento_id IN (SELECT id FROM {$atendimentoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            $query = $conn->prepare("
                DELETE FROM {$atendimentoTable}
                WHERE unidade_id = :unidade OR :unidade = 0
            ");
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // reinicia o contador das senhas
            $this->reiniciarContadores($conn, $unidadeId);
        });
    }
}
