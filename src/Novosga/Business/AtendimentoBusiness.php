<?php
namespace Novosga\Business;

use PDO;
use Exception;
use Novosga\Util\DateUtil;
use Novosga\Model\Unidade;
use Novosga\Model\Usuario;
use Novosga\Model\Util\UsuarioSessao;
use Novosga\Model\Servico;
use Novosga\Model\Atendimento;

/**
 * AtendimentoBusiness
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoBusiness extends ModelBusiness {
    
    // estados do atendimento
    const SENHA_EMITIDA = 1;
    const CHAMADO_PELA_MESA = 2;
    const ATENDIMENTO_INICIADO = 3;
    const ATENDIMENTO_ENCERRADO = 4;
    const NAO_COMPARECEU = 5;
    const SENHA_CANCELADA = 6;
    const ERRO_TRIAGEM = 7;
    const ATENDIMENTO_ENCERRADO_CODIFICADO = 8;
    
    public static function situacoes() {
        return array(
            self::SENHA_EMITIDA => _('Senha emitida'),
            self::CHAMADO_PELA_MESA => _('Chamado pela mesa'),
            self::ATENDIMENTO_INICIADO => _('Atendimento iniciado'),
            self::ATENDIMENTO_ENCERRADO => _('Atendimento encerrado'),
            self::NAO_COMPARECEU => _('Não compareceu'),
            self::SENHA_CANCELADA => _('Senha cancelada'),
            self::ERRO_TRIAGEM => _('Erro triagem'),
            self::ATENDIMENTO_ENCERRADO_CODIFICADO => _('Atendimento encerrado e codificado')
        );
    }
    
    public static function nomeSituacao($status) {
        $arr = self::situacoes();
        return $arr[$status];
    }
    
    public function chamarSenha(Unidade $unidade, Atendimento $atendimento) {
        $conn = $this->em->getConnection();
    	$stmt = $conn->prepare("
            INSERT INTO painel_senha 
            (unidade_id, servico_id, num_senha, sig_senha, msg_senha, local, num_local, peso) 
            VALUES 
            (:unidade_id, :servico_id, :num_senha, :sig_senha, :msg_senha, :local, :num_local, :peso)
        ");
        $stmt->bindValue('unidade_id', $unidade->getId());
        $stmt->bindValue('servico_id', $atendimento->getServicoUnidade()->getServico()->getId());
        $stmt->bindValue('num_senha', $atendimento->getSenha()->getNumero());
        $stmt->bindValue('sig_senha', $atendimento->getSenha()->getSigla());
        $stmt->bindValue('msg_senha', $atendimento->getSenha()->getLegenda());
        $stmt->bindValue('local', $atendimento->getServicoUnidade()->getLocal()->getNome());
        $stmt->bindValue('num_local', $atendimento->getLocal());
        $stmt->bindValue('peso', $atendimento->getSenha()->getPrioridade()->getPeso());
        $stmt->execute();
    }

    /**
     * Move os registros da tabela atendimento para a tabela de historico de atendimentos.
     * Se a unidade não for informada, será acumulado serviços de todas as unidades.
     * @param type $unidade
     * @throws Exception
     */
    public function acumularAtendimentos($unidade = 0) {
        if ($unidade instanceof \Novosga\Model\Unidade) {
            $unidade = $unidade->getId();
        }
        try {
            $conn = $this->em->getConnection();
            $data = DateUtil::nowSQL();
            $conn->beginTransaction();
            // salva atendimentos da unidade
            $sql = "
                INSERT INTO historico_atendimentos 
                (
                    id, unidade_id, usuario_id, servico_id, prioridade_id, status, sigla_senha, num_senha, num_senha_serv, 
                    nm_cli, num_local, dt_cheg, dt_cha, dt_ini, dt_fim, ident_cli, usuario_tri_id
                )
                SELECT 
                    a.id, a.unidade_id, a.usuario_id, a.servico_id, a.prioridade_id, a.status, a.sigla_senha, a.num_senha, a.num_senha_serv, 
                    a.nm_cli, a.num_local, a.dt_cheg, a.dt_cha, a.dt_ini, a.dt_fim, a.ident_cli, a.usuario_tri_id
                FROM 
                    atendimentos a
                WHERE 
                    a.dt_cheg <= :data
            ";
            if ($unidade > 0) {
                $sql .= " AND a.unidade_id = :unidade";
            }
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            // salva atendimentos codificados da unidade
            $subquery = "SELECT a.id FROM atendimentos a WHERE dt_cheg <= :data ";
            if ($unidade > 0) {
                $subquery .= " AND a.unidade_id = :unidade";
            }
            $query = $conn->prepare("
                INSERT INTO historico_atend_codif
                SELECT 
                    ac.atendimento_id, ac.servico_id, ac.valor_peso
                FROM 
                    atend_codif ac
                WHERE 
                    ac.atendimento_id IN (
                        $subquery
                    )
            ");
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();

            // limpa atendimentos codificados da unidade 
            $subquery = "SELECT id FROM atendimentos WHERE dt_cheg <= :data ";
            if ($unidade > 0) {
                $subquery .= " AND unidade_id = :unidade";
            }
            $sql = "DELETE FROM atend_codif WHERE atendimento_id IN ( $subquery )";
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();
            
            // limpa atendimentos da unidade
            // por causa do auto relacionamento, primeiro apaga os registros filhos
            $sql = 'DELETE FROM atendimentos WHERE dt_cheg <= :data AND atendimento_id IS NOT NULL';
            if ($unidade > 0) {
                $sql .= " AND unidade_id = :unidade";
            }
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();
            // agora apaga os demais registros (os atendimentos pais)
            $sql = 'DELETE FROM atendimentos WHERE dt_cheg <= :data ';
            if ($unidade > 0) {
                $sql .= " AND unidade_id = :unidade";
            }
            $query = $conn->prepare($sql);
            $query->bindValue('data', $data, PDO::PARAM_STR);
            if ($unidade > 0) {
                $query->bindValue('unidade', $unidade, PDO::PARAM_INT);
            }
            $query->execute();
            
            // limpa a tabela de senhas a serem exibidas no painel
            $query = $conn->prepare("DELETE FROM painel_senha");
            $query->execute();

            $conn->commit();
        } catch (Exception $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw new Exception($e->getMessage());
        }
    }
    
    public static function isNumeracaoServico() {
        if (defined('NOVOSGA_TIPO_NUMERACAO')) {
            return NOVOSGA_TIPO_NUMERACAO == \Novosga\Model\Util\Senha::NUMERACAO_SERVICO;
        }
        return false;
    }
    
    public function buscaAtendimento(Unidade $unidade, $id) {
        $query = $this->em->createQuery("SELECT e FROM Novosga\Model\Atendimento e JOIN e.servicoUnidade su WHERE e.id = :id AND su.unidade = :unidade");
        $query->setParameter('id', (int) $id);
        $query->setParameter('unidade', $unidade->getId());
        return $query->getOneOrNullResult();
    }
    
    public function buscaAtendimentos(Unidade $unidade, $senha) {
        $field = self::isNumeracaoServico() ? 'numeroSenhaServico' : 'numeroSenha';
        $cond = '';
        $sigla = strtoupper(substr($senha, 0, 1));
        // verificando se a letra foi informada (o primeiro caracter diferente do valor convertido para int)
        $porSigla = ctype_alpha($sigla);
        if ($porSigla) {
            $cond = 'e.siglaSenha = :sigla AND';
            $numeroSenha = (int) substr($senha, 1);
        } else {
            $numeroSenha = (int) $senha;
        }
        $query = $this->em->createQuery("
            SELECT 
                e 
            FROM 
                Novosga\Model\Atendimento e 
                JOIN e.servicoUnidade su 
            WHERE 
                e.$field = :numero AND $cond
                su.unidade = :unidade 
            ORDER BY 
                e.id
        ");
        $query->setParameter('numero', $numeroSenha);
        if ($porSigla) {
            $query->setParameter('sigla', $sigla);
        }
        $query->setParameter('unidade', $unidade->getId());
        return $query->getResult();
    }
    
    public function distribuiSenha($unidade, $usuario, $servico, $prioridade, $nomeCliente, $documentoCliente) {
        if (!($unidade instanceof Unidade)) {
            $unidade = $this->em->find("Novosga\Model\Unidade", (int) $unidade);
        }
        if (!$unidade) {
            throw new Exception(_('Nenhum unidade escolhida'));
        }
        if (!($usuario instanceof Usuario || $usuario instanceof UsuarioSessao)) {
            $usuario = $this->em->find("Novosga\Model\Usuario", (int) $usuario);
        }
        if (!$usuario) {
            throw new Exception(_('Nenhum usuário na sessão'));
        }
        // verificando a prioridade
        $prioridade = $this->em->find("Novosga\Model\Prioridade", $prioridade);
        if (!$prioridade || $prioridade->getStatus() == 0) {
            throw new Exception(_('Prioridade inválida'));
        }
        $servico = ($servico instanceof Servico) ? $servico->getId() : (int) $servico;
        // verificando se o servico esta disponivel na unidade
        $query = $this->em->createQuery("SELECT e FROM Novosga\Model\ServicoUnidade e WHERE e.unidade = :unidade AND e.servico = :servico");
        $query->setParameter('unidade', $unidade->getId());
        $query->setParameter('servico', $servico);
        $su = $query->getOneOrNullResult();
        if (!$su) {
            throw new Exception(_('Serviço não disponível para a unidade atual'));
        }
        $conn = $this->em->getConnection();
        /*
         * XXX: Os parametros abaixo (id da unidade e sigla) estao sendo concatenados direto na string devido a um bug do pdo_sqlsrv (windows)
         */
        // ultimo numero gerado (total)
        $innerQuery = "SELECT num_senha FROM atendimentos a WHERE a.unidade_id = {$unidade->getId()} ORDER BY num_senha DESC";
        $innerQuery = $conn->getDatabasePlatform()->modifyLimitQuery($innerQuery, 1, 0);
        // ultimo numero gerado (servico). busca pela sigla do servico para nao aparecer duplicada (em caso de mais de um servico com a mesma sigla)
        $innerQuery2 = "SELECT num_senha_serv FROM atendimentos a WHERE a.unidade_id = {$unidade->getId()} AND a.sigla_senha = '{$su->getSigla()}' ORDER BY num_senha_serv DESC";
        $innerQuery2 = $conn->getDatabasePlatform()->modifyLimitQuery($innerQuery2, 1, 0);
        $stmt = $conn->prepare(" 
            INSERT INTO atendimentos
            (unidade_id, servico_id, prioridade_id, usuario_tri_id, status, nm_cli, ident_cli, num_local, dt_cheg, sigla_senha, num_senha, num_senha_serv)
            SELECT
                :unidade_id, :servico_id, :prioridade_id, :usuario_tri_id, :status, :nm_cli, :ident_cli, :num_local, :dt_cheg, :sigla_senha, 
                COALESCE(
                    (
                        $innerQuery
                    ) , 0) + 1,
                COALESCE(
                    (
                        $innerQuery2
                    ) , 0) + 1
        ");
        $stmt->bindValue('unidade_id', $unidade->getId(), PDO::PARAM_INT);
        $stmt->bindValue('servico_id', $servico, PDO::PARAM_INT);
        $stmt->bindValue('prioridade_id', $prioridade->getId(), PDO::PARAM_INT);
        $stmt->bindValue('usuario_tri_id', $usuario->getId(), PDO::PARAM_INT);
        $stmt->bindValue('status', AtendimentoBusiness::SENHA_EMITIDA, PDO::PARAM_INT);
        $stmt->bindValue('nm_cli', $nomeCliente, PDO::PARAM_STR);
        $stmt->bindValue('ident_cli', $documentoCliente, PDO::PARAM_STR);
        $stmt->bindValue('num_local', 0, PDO::PARAM_INT);
        $stmt->bindValue('dt_cheg', DateUtil::nowSQL(), PDO::PARAM_STR);
        $stmt->bindValue('sigla_senha', $su->getSigla(), PDO::PARAM_STR);

        $success = ($stmt->execute() == true);
        if (!$success) {
            throw new Exception(_('Erro ao tentar gerar nova senha'));
        }
        $id = $conn->lastInsertId();
        if (!$id) {
            $id = $conn->lastInsertId('atendimentos_id_seq');
        }
        if (!$id) {
            throw new \Exception(_('Erro ao pegar o ID gerado pelo banco. Entre em contato com a equipe de desenvolvimento informando esse problema, e o banco de dados que está usando'));
        }
        return array(
            'id' => $id,
            'atendimento' => $this->em->find("Novosga\Model\Atendimento", $id)->toArray()
        );
    }
    
}
