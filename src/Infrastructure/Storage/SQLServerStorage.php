<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Jociel Schultz <jschultz@uniscbr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Infrastructure\Storage;

use Doctrine\DBAL\Connection;

use Exception;
use DateTime;

use Novosga\Entity\Contador;
use Novosga\Entity\Servico;
use Novosga\Entity\Unidade;

use Novosga\Entity\Agendamento;
use Novosga\Entity\Atendimento;
use Novosga\Entity\AtendimentoCodificado;
use Novosga\Entity\AtendimentoCodificadoHistorico;
use Novosga\Entity\AtendimentoHistorico;
use Novosga\Entity\AtendimentoHistoricoMeta;
use Novosga\Entity\AtendimentoMeta;
use Novosga\Entity\PainelSenha;
use Novosga\Entity\ServicoUnidade;

use PDO;

/**
 * SQLServer Storage
 *
 * @author Jociel Schultz <rogeriolino@gmail.com>
 */
class SQLServerStorage extends RelationalStorage
{
    /**
     * {@inheritdoc}
     */
    protected function numeroAtual(Connection $conn, Unidade $unidade, Servico $servico): int
    {
        $contadorTable = $this->om->getClassMetadata(Contador::class)->getTableName();
     
        $stmt = $conn->prepare("
            SELECT numero 
            FROM {$contadorTable} 
            WHERE
                unidade_id = ? AND
                servico_id = ?
        ");

        $stmt->bindValue(1, $unidade->getId());
        $stmt->bindValue(2, $servico->getId());
        $stmt->execute();
        $numeroAtual = (int) $stmt->fetchColumn();
        
        return $numeroAtual;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function preAcumularAtendimentos(Connection $conn, Unidade $unidade = null)
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    protected function preApagarDadosAtendimento(Connection $conn, Unidade $unidade = null)
    {
        
    }
    
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
            WHERE (unidade_id = ? OR ? = 0)
        ");
    	$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    	$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    	$query->execute();
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
                SET numero = ?
                WHERE
                    unidade_id = ? AND
                    servico_id = ? AND
                    numero = ?
            ");
    		$stmt->bindValue(1, $numeroSenha);
    		$stmt->bindValue(2, $unidade->getId());
    		$stmt->bindValue(3, $servico->getId());
    		$stmt->bindValue(4, $numeroAtual);
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
    public function acumularAtendimentos(Unidade $unidade = null)
    {
    	$self = $this;
    	$conn = $this->om->getConnection();
    	
    	$conn->transactional(function ($conn) use ($self, $unidade) {
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
    		
    		$helper = new \App\Helper\DoctrineHelper($this->om);
    		
    		// columns
    		$historicoColumns       = $helper->getEntityColumns(AtendimentoHistorico::class);
    		$historicoMetaColumns   = $helper->getEntityColumns(AtendimentoHistoricoMeta::class);
    		$historicoCodifColumns  = $helper->getEntityColumns(AtendimentoCodificadoHistorico::class);
    		
    		$self->preAcumularAtendimentos($conn, $unidade);
    		
    		// copia os atendimentos para o historico
    		$sql = "
				SET IDENTITY_INSERT {$historicoTable}  ON;
                INSERT INTO {$historicoTable}
                (
                    " . join(', ', $historicoColumns) . "
                )
                SELECT
                    a." . join(', a.', $historicoColumns) . "
                FROM
                    {$atendimentoTable} a
                WHERE
                    a.dt_cheg <= ? AND (a.unidade_id = ? OR ? = 0)
            ";
                    
                    // atendimentos filhos (oriundos de redirecionamento)
                    $query = $conn->prepare("{$sql} AND a.atendimento_id IS NOT NULL");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // atendimentos pais (nao oriundos de redirecionamento)
                    $query = $conn->prepare("$sql AND a.atendimento_id IS NULL; 
											SET IDENTITY_INSERT {$historicoTable}  OFF;");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
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
                            b.dt_cheg <= ? AND
                            (b.unidade_id = ? OR ? = 0)
                    )
            ";
                    $query = $conn->prepare($sql);
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
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
                    a.dt_cheg <= ? AND
                    (a.unidade_id = ? OR ? = 0)
            ");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // limpa atendimentos codificados
                    $query = $conn->prepare("
                DELETE FROM {$atendimentoCodifTable}
                WHERE atendimento_id IN (
                    SELECT id
                    FROM {$atendimentoTable}
                    WHERE
                        dt_cheg <= ? AND
                        (unidade_id = ? OR ? = 0)
                )
            ");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // limpa metadata
                    $query = $conn->prepare("
                DELETE FROM {$atendimentoMetaTable}
                WHERE atendimento_id IN (
                    SELECT id
                    FROM {$atendimentoTable}
                    WHERE
                        dt_cheg <= ? AND
                        (unidade_id = ? OR ? = 0)
                )
            ");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // limpa o auto-relacionamento para poder excluir os atendimento sem dar erro de constraint (#136)
                    $query = $conn->prepare("
                DELETE FROM {$atendimentoTable}
                WHERE
                    atendimento_id IS NOT NULL AND
                    dt_cheg <= ? AND
                    (unidade_id = ? OR ? = 0)
            ");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // limpa atendimentos da unidade
                    $query = $conn->prepare("
                DELETE FROM {$atendimentoTable}
                WHERE
                    dt_cheg <= ? AND
                    (unidade_id = ? OR ? = 0)
            ");
                    $query->bindValue(1, $data, PDO::PARAM_STR);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(3, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // limpa a tabela de senhas a serem exibidas no painel
                    $query = $conn->prepare("
                DELETE FROM {$painelSenhaTable}
                WHERE (unidade_id = ? OR ? = 0)
            ");
                    $query->bindValue(1, $unidadeId, PDO::PARAM_INT);
                    $query->bindValue(2, $unidadeId, PDO::PARAM_INT);
                    $query->execute();
                    
                    // reinicia o contador das senhas
                    $this->reiniciarContadores($conn, $unidadeId);
    	});
    }
    
    /**
     * {@inheritdoc}
     */
    public function apagarDadosAtendimento(Unidade $unidade = null)
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
                WHERE atendimento_id IN (SELECT id FROM {$historicoTable} WHERE unidade_id = ? OR ? = 0)
            ");
    		$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    		$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    		$query->execute();
    		
    		$query = $conn->prepare("
                DELETE FROM {$historicoMetaTable}
                WHERE atendimento_id IN (SELECT id FROM {$historicoTable} WHERE unidade_id = ? OR ? = 0)
            ");
    		$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    		$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    		$query->execute();
    		
    		$query = $conn->prepare("
                DELETE FROM {$historicoTable}
                WHERE unidade_id = ? OR ? = 0
            ");
    		$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    		$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    		$query->execute();
    		
    		$query = $conn->prepare("
                DELETE FROM {$atendimentoCodifTable}
                WHERE atendimento_id IN (SELECT id FROM {$atendimentoTable} WHERE unidade_id = ? OR ? = 0)
            ");
    		$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    		$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    		$query->execute();
    		
    		$query = $conn->prepare("
                DELETE FROM {$atendimentoMetaTable}
                WHERE atendimento_id IN (SELECT id FROM {$atendimentoTable} WHERE unidade_id = ? OR ? = 0)
            ");
    		$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    		$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    		$query->execute();
    		
    		$query = $conn->prepare("
                DELETE FROM {$atendimentoTable}
                WHERE unidade_id = ? OR ? = 0
            ");
    		$query->bindValue(1, $unidadeId, PDO::PARAM_INT);
    		$query->bindValue(2, $unidadeId, PDO::PARAM_INT);
    		$query->execute();
    		
    		// reinicia o contador das senhas
    		$this->reiniciarContadores($conn, $unidadeId);
    	});
    }
    
}
