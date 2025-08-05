<?php

declare(strict_types=1);

/*
 * This file is part of the NovoSGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Entity;

use App\Entity\Atendimento;
use App\Entity\Local;
use App\Entity\Prioridade;
use App\Entity\Senha;
use App\Entity\Servico;
use App\Entity\Unidade;
use App\Entity\Usuario;
use App\Service\AtendimentoService;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class AtendimentoTest extends TestCase
{
    public function testJsonSerializeNewTicket(): void
    {
        $atendimento = $this->buildAtendimento();
        $expected = $this->buildArray();

        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            json_encode($atendimento->jsonSerialize()),
        );
    }

    public function testJsonSerializeCalledTicket(): void
    {
        $atendimento = $this
            ->buildAtendimento()
            ->setStatus(AtendimentoService::CHAMADO_PELA_MESA)
            ->setLocal(
                (new Local())
                    ->setId(1)
                    ->setNome('Guichê')
                    ->setCreatedAt((new DateTimeImmutable('2025-08-01 10:00:00')))
                    ->setUpdatedAt((new DateTimeImmutable('2025-08-02 12:00:00')))
            )
            ->setNumeroLocal(3)
            ->setUsuario(
                (new Usuario())
                    ->setId(2)
                    ->setNome('Atendente')
                    ->setLogin('atendente')
            );
        $expected = array_merge($this->buildArray(), [
            'status' => AtendimentoService::CHAMADO_PELA_MESA,
            'local' => [
                'id' => 1,
                'nome' => 'Guichê',
                'createdAt' => '2025-08-01T10:00:00',
                'updatedAt' => '2025-08-02T12:00:00',
            ],
            'numeroLocal' => 3,
            'usuario' => [
                'id' => 2,
                'login' => 'atendente',
            ],
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            json_encode($atendimento->jsonSerialize()),
        );
    }

    private function buildAtendimento(): Atendimento
    {
        return (new Atendimento())
            ->setId(1)
            ->setDataChegada(new DateTimeImmutable('2025-08-04 10:00:00'))
            ->setTempoEspera(new DateInterval('PT1H45M10S'))
            ->setStatus(AtendimentoService::SENHA_EMITIDA)
            ->setSenha(
                (new Senha())
                    ->setNumero(1)
                    ->setSigla('A')
            )
            ->setUnidade(
                (new Unidade())
                    ->setId(1)
                    ->setNome('Unidade 1')
                    ->setCreatedAt((new DateTimeImmutable('2025-08-01 10:00:00')))
                    ->setUpdatedAt((new DateTimeImmutable('2025-08-02 12:00:00')))
            )
            ->setServico(
                (new Servico())
                    ->setId(1)
                    ->setNome('Atendimento Geral')
                    ->setCreatedAt((new DateTimeImmutable('2025-08-01 10:00:00')))
                    ->setUpdatedAt((new DateTimeImmutable('2025-08-02 12:00:00')))
            )
            ->setPrioridade(
                (new Prioridade())
                    ->setId(1)
                    ->setNome('Normal')
                    ->setPeso(0)
                    ->setCreatedAt((new DateTimeImmutable('2025-08-01 10:00:00')))
                    ->setUpdatedAt((new DateTimeImmutable('2025-08-02 12:00:00')))
            )
            ->setUsuarioTriagem(
                (new Usuario())
                    ->setId(1)
                    ->setNome('Triagem')
                    ->setLogin('triagem')
                    ->setCreatedAt((new DateTimeImmutable('2025-08-01 10:00:00')))
                    ->setUpdatedAt((new DateTimeImmutable('2025-08-02 12:00:00')))
            );
    }

    /** @return array<string,mixed> */
    private function buildArray(): array
    {
        return [
            'id' => 1,
            'senha' => [
                'numero' => 1,
                'sigla' => 'A',
                'format' => 'A001',
            ],
            'servico' => [
                'id' => 1,
                'nome' => 'Atendimento Geral',
            ],
            'unidade' => [
                'id' => 1,
                'nome' => 'Unidade 1',
            ],
            'status' => AtendimentoService::SENHA_EMITIDA,
            'observacao' => null,
            'dataChegada' => '2025-08-04T10:00:00',
            'dataChamada' => null,
            'dataInicio' => null,
            'dataFim' => null,
            'dataAgendamento' => null,
            'tempoEspera' => '01:45:10',
            'prioridade' => [
                'id' => 1,
                'nome' => 'Normal',
                'peso' => 0,
                'ativo' => true,
                'cor' => null,
                'descricao' => null,
                'createdAt' => '2025-08-01T10:00:00',
                'updatedAt' => '2025-08-02T12:00:00',
                'deletedAt' => null,
            ],
            'local' => null,
            'numeroLocal' => null,
            'resolucao' => null,
            'cliente' => null,
            'triagem' => [
                'id' => 1,
                'login' => 'triagem',
            ],
            'usuario' => null,
            'hash' => '7d299d6e52b432321bfc425f9b4ab12e3255958a',
        ];
    }
}
