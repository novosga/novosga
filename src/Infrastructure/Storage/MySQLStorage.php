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
use Novosga\Entity\ServicoUnidade;
use Novosga\Entity\Unidade;
use PDO;

/**
 * MySQL Storage
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class MySQLStorage extends RelationalStorage
{
    /**
     * {@inheritdoc}
     */
    public function distribui(Atendimento $atendimento, Agendamento $agendamento = null)
    {
        $conn          = $this->om->getConnection();
        $contadorTable = $this->om->getClassMetadata(Contador::class)->getTableName();
        $unidade       = $atendimento->getUnidade();
        $servico       = $atendimento->getServico();
        
        $su = $this
            ->getRepository(ServicoUnidade::class)
            ->get($unidade, $servico);
        
        $stmt = $conn->prepare("
            SELECT numero 
            FROM {$contadorTable} 
            WHERE
                unidade_id = :unidade AND
                servico_id = :servico
            FOR UPDATE
        ");
        $stmt->bindValue('unidade', $unidade->getId());
        $stmt->bindValue('servico', $servico->getId());
        $stmt->execute();
        $numeroAtual = (int) $stmt->fetchColumn();
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
        $atendimento->getSenha()->setNumero($numeroSenha);

        if ($agendamento) {
            $agendamento->setDataConfirmacao(new DateTime());
        }

        $this->om->persist($atendimento);
        $this->om->flush();
    }
    
    /**
     * {@inheritdoc}
     */
    public function acumularAtendimentos(Unidade $unidade = null)
    {
        $data      = (new DateTime())->format('Y-m-d H:i:s');
        $unidadeId = $unidade ? $unidade->getId() : 0;
        
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
        
        $conn = $this->om->getConnection();
        $conn->beginTransaction();

        try {
            $conn->exec('SET foreign_key_checks = 0');
            
            // copia os atendimentos para o historico
            $sql = "
                INSERT INTO {$historicoTable}
                (
                    id, unidade_id, usuario_id, servico_id, prioridade_id, status,
                    senha_sigla, senha_numero, cliente_id, num_local, dt_cheg,
                    dt_cha, dt_ini, dt_fim, usuario_tri_id, atendimento_id,
                    tempo_espera, tempo_permanencia, tempo_atendimento, tempo_deslocamento
                )
                SELECT
                    a.id, a.unidade_id, a.usuario_id, a.servico_id, a.prioridade_id, a.status,
                    a.senha_sigla, a.senha_numero, a.cliente_id, a.num_local, a.dt_cheg,
                    a.dt_cha, a.dt_ini, a.dt_fim, a.usuario_tri_id, a.atendimento_id,
                    a.tempo_espera, a.tempo_permanencia, a.tempo_atendimento, a.tempo_deslocamento
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
                    atendimento_id, name, value
                )
                SELECT
                    a.atendimento_id, a.name, a.value
                FROM
                    {$atendimentoMetaTable}  a
                WHERE
                    a.atendimento_id IN (
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
                    atendimento_id, servico_id, valor_peso
                )
                SELECT
                    ac.atendimento_id, ac.servico_id, ac.valor_peso
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

            // reinicia o contador das senhas
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

            $conn->commit();
        } catch (Exception $e) {
            try {
                $conn->rollBack();
            } catch (Exception $e2) {
            }
            throw $e;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function apagarDadosAtendimento(Unidade $unidade = null)
    {
        // tables name
        $historicoTable        = $this->om->getClassMetadata(AtendimentoHistorico::class)->getTableName();
        $historicoCodifTable   = $this->om->getClassMetadata(AtendimentoCodificadoHistorico::class)->getTableName();
        $historicoMetaTable    = $this->om->getClassMetadata(AtendimentoHistoricoMeta::class)->getTableName();
        $atendimentoTable      = $this->om->getClassMetadata(Atendimento::class)->getTableName();
        $atendimentoCodifTable = $this->om->getClassMetadata(AtendimentoCodificado::class)->getTableName();
        $atendimentoMetaTable  = $this->om->getClassMetadata(AtendimentoMeta::class)->getTableName();
        
        $unidadeId = $unidade ? $unidade->getId() : 0;
        
        $conn = $this->om->getConnection();
        $conn->beginTransaction();
        
        try {
            $conn->exec('SET foreign_key_checks = 0');
            
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
            
            $conn->commit();
        } catch (Exception $e) {
            try {
                $conn->rollBack();
            } catch (Exception $e2) {
            }
            throw $e;
        }
    }
}
