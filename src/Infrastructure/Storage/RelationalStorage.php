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
use DateTimeInterface;
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
use App\Helper\DoctrineHelper;
use App\Repository\ServicoUnidadeRepository;
use Novosga\Entity\AgendamentoInterface;
use Novosga\Entity\AtendimentoInterface;
use Novosga\Entity\ServicoInterface;
use Novosga\Entity\UnidadeInterface;
use Novosga\Entity\UsuarioInterface;
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

    /** {@inheritDoc} */
    public function acumularAtendimentos(
        UsuarioInterface $usuario,
        ?UnidadeInterface $unidade,
        DateTimeInterface $ateData,
    ): void {
        $self = $this;
        $conn = $this->em->getConnection();

        $conn->transactional(function (Connection $conn) use ($self, $unidade, $ateData) {
            $unidadeId = (int) $unidade?->getId();
            $data = $ateData->format('Y-m-d H:i:s');

            // tables name
            $historicoTable = $this->em->getClassMetadata(AtendimentoHistorico::class)->getTableName();
            $historicoCodifTable = $this->em->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
            $historicoMetaTable = $this->em->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
            $atendimentoTable = $this->em->getClassMetadata(Atendimento::class)->getTableName();
            $atendimentoCodifTable = $this->em->getClassMetadata(AtendimentoCodificado::class)->getTableName();
            $atendimentoMetaTable = $this->em->getClassMetadata(AtendimentoMeta::class)->getTableName();
            $painelSenhaTable = $this->em->getClassMetadata(PainelSenha::class)->getTableName();

            $helper = new DoctrineHelper($this->em);

            // columns
            $historicoColumns = $helper->getEntityColumns(AtendimentoHistorico::class);
            $historicoMetaColumns = $helper->getEntityColumns(AtendimentoHistoricoMeta::class);
            $historicoCodifColumns = $helper->getEntityColumns(AtendimentoCodificadoHistorico::class);

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
            $conn->executeQuery("$sql AND a.atendimento_id IS NULL", [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // atendimentos filhos (oriundos de redirecionamento)
            $conn->executeQuery("{$sql} AND a.atendimento_id IS NOT NULL", [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

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
            $conn->executeQuery($sql, [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // copia os atendimentos codificados para o historico
            $sql = "
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
            ";
            $conn->executeQuery($sql, [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // limpa atendimentos codificados
            $sql = "
                DELETE FROM {$atendimentoCodifTable}
                WHERE atendimento_id IN (
                    SELECT id
                    FROM {$atendimentoTable}
                    WHERE
                        dt_cheg <= :data AND
                        (unidade_id = :unidade OR :unidade = 0)
                )
            ";
            $conn->executeQuery($sql, [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // limpa metadata
            $sql = "
                DELETE FROM {$atendimentoMetaTable}
                WHERE atendimento_id IN (
                    SELECT id
                    FROM {$atendimentoTable}
                    WHERE
                        dt_cheg <= :data AND
                        (unidade_id = :unidade OR :unidade = 0)
                )
            ";
            $conn->executeQuery($sql, [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // limpa o auto-relacionamento para poder excluir os atendimento sem dar erro de constraint (#136)
            $sql = "
                DELETE FROM {$atendimentoTable}
                WHERE
                    atendimento_id IS NOT NULL AND
                    dt_cheg <= :data AND
                    (unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // limpa atendimentos da unidade
            $sql = "
                DELETE FROM {$atendimentoTable}
                WHERE
                    dt_cheg <= :data AND
                    (unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [
                'data' => $data,
                'unidade' => $unidadeId,
            ]);

            // limpa a tabela de senhas a serem exibidas no painel
            $sql = "
                DELETE FROM {$painelSenhaTable}
                WHERE (unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [
                'unidade' => $unidadeId,
            ]);

            $rs = $conn->executeQuery("SELECT COUNT(*) FROM {$atendimentoTable}");
            $total = (int) $rs->fetchOne();

            // reinicia o contador das senhas
            if ($total === 0) {
                $this->reiniciarContadores($conn, $unidadeId);
            }
        });
    }

    /** {@inheritdoc} */
    public function apagarDadosAtendimento(
        UsuarioInterface $usuario,
        ?UnidadeInterface $unidade,
    ): void {
        $self = $this;
        $conn = $this->em->getConnection();

        $conn->transactional(function ($conn) use ($self, $unidade) {
            // tables name
            $historicoTable = $this->em->getClassMetadata(AtendimentoHistorico::class)->getTableName();
            $historicoCodifTable = $this->em->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
            $historicoMetaTable = $this->em->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
            $atendimentoTable = $this->em->getClassMetadata(Atendimento::class)->getTableName();
            $atendimentoCodifTable = $this->em->getClassMetadata(AtendimentoCodificado::class)->getTableName();
            $atendimentoMetaTable = $this->em->getClassMetadata(AtendimentoMeta::class)->getTableName();

            $unidadeId = (int) $unidade?->getId();

            $self->preApagarDadosAtendimento($conn, $unidade);

            $sql = "
                DELETE FROM {$historicoCodifTable}
                WHERE atendimento_id IN (SELECT id FROM {$historicoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [ 'unidade' => $unidadeId ]);

            $sql = "
                DELETE FROM {$historicoMetaTable}
                WHERE atendimento_id IN (SELECT id FROM {$historicoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [ 'unidade' => $unidadeId ]);

            $sql = "
                DELETE FROM {$historicoTable}
                WHERE unidade_id = :unidade OR :unidade = 0
            ";
            $conn->executeQuery($sql, [ 'unidade' => $unidadeId ]);

            $sql = "
                DELETE FROM {$atendimentoCodifTable}
                WHERE atendimento_id IN (SELECT id FROM {$atendimentoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [ 'unidade' => $unidadeId ]);

            $sql = "
                DELETE FROM {$atendimentoMetaTable}
                WHERE atendimento_id IN (SELECT id FROM {$atendimentoTable} WHERE unidade_id = :unidade OR :unidade = 0)
            ";
            $conn->executeQuery($sql, [ 'unidade' => $unidadeId ]);

            $sql = "
                DELETE FROM {$atendimentoTable}
                WHERE unidade_id = :unidade OR :unidade = 0
            ";
            $conn->executeQuery($sql, [ 'unidade' => $unidadeId ]);

            // reinicia o contador das senhas
            $this->reiniciarContadores($conn, $unidadeId);
        });
    }
}
