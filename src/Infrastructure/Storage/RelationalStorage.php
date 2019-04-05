<?php

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
use Novosga\Entity\Agendamento;
use Novosga\Entity\Atendimento;
use Novosga\Entity\AtendimentoCodificado;
use Novosga\Entity\AtendimentoCodificadoHistorico;
use Novosga\Entity\AtendimentoHistorico;
use Novosga\Entity\AtendimentoHistoricoMeta;
use Novosga\Entity\AtendimentoMeta;
use Novosga\Entity\Contador;
use Novosga\Entity\PainelSenha;
use Novosga\Entity\Servico;
use Novosga\Entity\ServicoUnidade;
use Novosga\Entity\Unidade;
use PDO;

/**
 * ORM Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
abstract class RelationalStorage extends DoctrineStorage
{
    /**
     * @param Connection $conn
     * @param Unidade    $unidade
     * @param Servico    $servico
     * @return int
     */
    abstract protected function numeroAtual(Connection $conn, Unidade $unidade, Servico $servico): int;
    
    /**
     * @param Connection   $conn
     * @param Unidade|null $unidade
     */
    abstract protected function preAcumularAtendimentos(Connection $conn, Unidade $unidade = null);
    
    /**
     * @param Connection   $conn
     * @param Unidade|null $unidade
     */
    abstract protected function preApagarDadosAtendimento(Connection $conn, Unidade $unidade = null);
    
    /**
     * Reinicia os contadores dos serviços da unidade
     * @param Connection   $conn
     * @param int          $unidadeId
     */
    protected function reiniciarContadores(Connection $conn, int $unidadeId)
    {
        $contadorTable       = $this->om->getClassMetadata(Contador::class)->getTableName();
        $servicoUnidadeTable = $this->om->getClassMetadata(ServicoUnidade::class)->getTableName();
        
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
    
    /**
     * {@inheritdoc}
     */
    public function chamar(Atendimento $atendimento)
    {
        $this->om->getConnection()->beginTransaction();

        try {
            $this->om->lock($atendimento, LockMode::PESSIMISTIC_WRITE);
            $this->om->merge($atendimento);
            $this->om->getConnection()->commit();
            $this->om->flush();
        } catch (Exception $e) {
            $this->om->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function encerrar(Atendimento $atendimento, array $codificados, Atendimento $novoAtendimento = null)
    {
        $this->om->beginTransaction();
        
        try {
            foreach ($codificados as $codificado) {
                $this->om->persist($codificado);
            }
            
            if ($novoAtendimento) {
                $this->om->persist($novoAtendimento);
            }
            
            $this->om->merge($atendimento);
            $this->om->commit();
            $this->om->flush();
        } catch (Exception $e) {
            try {
                $this->om->rollback();
            } catch (Exception $ex) {
            }
            throw new $e;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function distribui(Atendimento $atendimento, Agendamento $agendamento = null)
    {
        $self = $this;
        $conn = $this->om->getConnection();
        
        $conn->transactional(function ($conn) use ($self, $atendimento, $agendamento) {
            $contadorTable = $this->om->getClassMetadata(Contador::class)->getTableName();
            $unidade       = $atendimento->getUnidade();
            $servico       = $atendimento->getServico();
            
            $su = $this
                ->getRepository(ServicoUnidade::class)
                ->get($unidade, $servico);

            $numeroAtual = $self->numeroAtual($conn, $unidade, $servico);
            $numeroSenha = $numeroAtual;

            $numeroSenha += $su->getIncremento();
            if ($su->getNumeroFinal() > 0 && $numeroSenha > $su->getNumeroFinal()) {
                $numeroSenha = $su->getNumeroInicial();
            }

            $stmt = $conn->prepare("
                UPDATE {$contadorTable} 
                SET numero = :numero
                WHERE
                    unidade_id = :unidade AND
                    servico_id = :servico AND
                    numero = :numeroAtual
            ");
            $stmt->bindValue('numero', $numeroSenha);
            $stmt->bindValue('unidade', $unidade->getId());
            $stmt->bindValue('servico', $servico->getId());
            $stmt->bindValue('numeroAtual', $numeroAtual);
            $stmt->execute();
            $success = $stmt->rowCount() === 1;

            if (!$success) {
                throw new Exception();
            }

            $atendimento->setDataChegada(new DateTime());
            $atendimento->getSenha()->setNumero($numeroAtual);

            if ($agendamento) {
                $agendamento->setDataConfirmacao(new DateTime());
            }

            $this->om->persist($atendimento);
            $this->om->flush();
        });
    }
    
    /**
     * {@inheritdoc}
     */
    public function acumularAtendimentos(?Unidade $unidade, array $ctx = [])
    {
        $self = $this;
        $conn = $this->om->getConnection();
        
        $conn->transactional(function ($conn) use ($self, $unidade, $ctx) {
            $data      = new DateTime();
            $unidadeId = $unidade ? $unidade->getId() : 0;

            if (isset($ctx['data']) && $ctx['data'] instanceof DateTime) {
                $data = $ctx['data'];
            }

            $data = $data->format('Y-m-d H:i:s');

            // tables name
            $historicoTable        = $this->om->getClassMetadata(AtendimentoHistorico::class)->getTableName();
            $historicoCodifTable   = $this->om->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
            $historicoMetaTable    = $this->om->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
            $atendimentoTable      = $this->om->getClassMetadata(Atendimento::class)->getTableName();
            $atendimentoCodifTable = $this->om->getClassMetadata(AtendimentoCodificado::class)->getTableName();
            $atendimentoMetaTable  = $this->om->getClassMetadata(AtendimentoMeta::class)->getTableName();
            $contadorTable         = $this->om->getClassMetadata(Contador::class)->getTableName();
            $painelSenhaTable      = $this->om->getClassMetadata(PainelSenha::class)->getTableName();
            $servicoUnidadeTable   = $this->om->getClassMetadata(ServicoUnidade::class)->getTableName();
            
            $helper = new \App\Helper\DoctrineHelper($this->om);

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

            // atendimentos filhos (oriundos de redirecionamento)
            $query = $conn->prepare("{$sql} AND a.atendimento_id IS NOT NULL");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            $query->bindValue('unidade', $unidadeId, PDO::PARAM_INT);
            $query->execute();

            // atendimentos pais (nao oriundos de redirecionamento)
            $query = $conn->prepare("$sql AND a.atendimento_id IS NULL");
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

            $query = $conn->prepare("SELECT COUNT(*) FROM {$atendimentoTable}");
            $query->execute();
            $total = (int) $query->fetchColumn();

            // reinicia o contador das senhas
            if ($total === 0) {
                $this->reiniciarContadores($conn, $unidadeId);
            }
        });
    }
    
    /**
     * {@inheritdoc}
     */
    public function apagarDadosAtendimento(?Unidade $unidade, array $ctx = [])
    {
        $self = $this;
        $conn = $this->om->getConnection();
        
        $conn->transactional(function ($conn) use ($self, $unidade) {
            // tables name
            $historicoTable        = $this->om->getClassMetadata(AtendimentoHistorico::class)->getTableName();
            $historicoCodifTable   = $this->om->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
            $historicoMetaTable    = $this->om->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
            $atendimentoTable      = $this->om->getClassMetadata(Atendimento::class)->getTableName();
            $atendimentoCodifTable = $this->om->getClassMetadata(AtendimentoCodificado::class)->getTableName();
            $atendimentoMetaTable  = $this->om->getClassMetadata(AtendimentoMeta::class)->getTableName();

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
