<?php

namespace Novosga\Api;

use Exception;
use Novosga\Service\AtendimentoService;

/**
 * Api V1.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ApiV1 extends Api
{
    /**
     * Retorna todas as prioridades disponíveis.
     *
     * @return array
     */
    public function prioridades()
    {
        return $this->em->createQuery('
            SELECT
                e.id, e.nome
            FROM
                Novosga\Model\Prioridade e
            WHERE
                e.status = 1 AND
                e.peso > 0
            ORDER BY
                e.nome ASC
        ')->getResult();
    }

    /**
     * Retorna todos os locais de atendimento.
     *
     * @return array
     */
    public function locais()
    {
        return $this->em->createQuery('
            SELECT
                e.id, e.nome
            FROM
                Novosga\Model\Local e
            ORDER BY
                e.nome ASC
        ')->getResult();
    }

    /**
     * Retorna todas as unidades ativas.
     *
     * @return array
     */
    public function unidades()
    {
        return $this->em->createQuery('
            SELECT
                e.id, e.codigo, e.nome, e.mensagemImpressao
            FROM
                Novosga\Model\Unidade e
            WHERE
                e.status = 1
            ORDER BY
                e.nome ASC
        ')->getResult();
    }

    /**
     * Retorna os serviços globais ou os serviços disponíveis na unidade informada.
     *
     * @param int $unidade
     *
     * @return array
     */
    public function servicos($unidade = 0)
    {
        if ($unidade <= 0) {
            // servicos globais
            return $this->em->createQuery('
                SELECT
                    e.id, e.nome
                FROM
                    Novosga\Model\Servico e
                ORDER BY
                    e.nome ASC
            ')->getResult();
        } else {
            // servicos da unidade
            return $this->em->createQuery('
                SELECT
                    s.id, e.sigla, s.nome, l.nome as local
                FROM
                    Novosga\Model\ServicoUnidade e
                    JOIN e.servico s
                    JOIN e.local l
                WHERE
                    e.status = 1 AND
                    e.unidade = :unidade
                ORDER BY
                    s.nome ASC
            ')->setParameter(':unidade', $unidade)
                ->getResult();
        }
    }

    /**
     * Retorna as senhas para serem exibidas no painel (max result 10).
     *
     * @param int    $unidade
     * @param string $servicos (1,2,3,4...)
     *
     * @return array
     */
    public function painel($unidade, array $servicos)
    {
        $length = \Novosga\Model\Util\Senha::LENGTH;
        // servicos da unidade
        return $this->em->createQuery("
            SELECT
                e.id, e.siglaSenha as sigla, e.mensagem, e.numeroSenha as numero,
                e.local, e.numeroLocal as numeroLocal, e.peso, s.nome as servico,
                e.prioridade, e.nomeCliente, e.documentoCliente,
                $length as length
            FROM
                Novosga\Model\PainelSenha e
                JOIN e.servico s
            WHERE
                e.unidade = :unidade AND
                s.id IN (:servicos)
            ORDER BY
                e.id DESC
        ")->setParameter(':unidade', (int) $unidade)
            ->setParameter(':servicos', $servicos)
            ->setMaxResults(10)
            ->getResult();
    }

    public function filaServicos($unidade, $servicos)
    {
        if (!empty($servicos)) {
            // fila de atendimento
            $filaService = new \Novosga\Service\FilaService($this->em);

            return $filaService
                        ->atendimento($unidade, $servicos)
                        ->getQuery()
                        ->getResult()
            ;
        }

        return array();
    }

    /**
     * Retorna a fila de atendimento do usuário.
     *
     * @param int $unidade
     * @param int $usuario
     *
     * @return array
     */
    public function filaUsuario($unidade, $usuario)
    {
        // servicos que o usuario atende
        $servicos = $this->em->createQuery('
            SELECT
                s.id
            FROM
                Novosga\Model\ServicoUsuario e
                JOIN e.servico s
            WHERE
                e.usuario = :usuario AND
                e.unidade = :unidade AND
                s.status = 1
        ')
            ->setParameter('unidade', $unidade)
            ->setParameter('usuario', $usuario)
            ->getResult()
        ;

        return $this->filaServicos($unidade, $servicos);
    }

    /**
     * Retorna o atendimento.
     *
     * @param int $id Id do atendimento
     *
     * @return array
     */
    public function atendimento($id)
    {
        // servicos que o usuario atende
        $atendimento = $this->em->find('Novosga\Model\Atendimento', $id);
        if (!$atendimento) {
            throw new Exception(_('Atendimento inválido'));
        }

        return $atendimento->jsonSerialize(true);
    }

    /**
     * Retorna informações sobre o atendimento ainda não atendido.
     *
     * @param int $id Id do atendimento
     *
     * @return array
     */
    public function atendimentoInfo($id)
    {
        // servicos que o usuario atende
        $atendimento = $this->em->find('Novosga\Model\Atendimento', $id);
        if (!$atendimento) {
            throw new Exception(_('Atendimento inválido'));
        }
        if ($atendimento->getStatus() !== \Novosga\Service\AtendimentoService::SENHA_EMITIDA) {
            throw new Exception(_('Senha já atendida'));
        }
        $filaService = new \Novosga\Service\FilaService($this->em);
        $atendimentos = $filaService->filaServico($atendimento->getUnidade(), $atendimento->getServico());

        $pos = 1;
        foreach ($atendimentos as $a) {
            if ($atendimento->getId() === $a->getId()) {
                break;
            }
            ++$pos;
        }

        return array(
            'pos' => $pos,
            'total' => sizeof($atendimentos),
            'atendimento' => $atendimento->jsonSerialize(true),
        );
    }

    /**
     * Distribui uma nova senha.
     */
    public function distribui($unidade, $usuario, $servico, $prioridade, $nomeCliente, $documentoCliente)
    {
        $service = new AtendimentoService($this->em);

        return $service->distribuiSenha($unidade, $usuario, $servico, $prioridade, $nomeCliente, $documentoCliente)->jsonSerialize();
    }
}
