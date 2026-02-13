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

use App\Entity\Unidade;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadeTest extends TestCase
{
    public function testJsonSerializeWithoutTimezone(): void
    {
        $unidade = (new Unidade())
            ->setId(1)
            ->setNome('Test Unit')
            ->setDescricao('Test Description')
            ->setAtivo(true)
            ->setCreatedAt(new DateTimeImmutable('2025-08-01 10:00:00'))
            ->setUpdatedAt(new DateTimeImmutable('2025-08-02 12:00:00'));

        $result = $unidade->jsonSerialize();

        $this->assertSame(1, $result['id']);
        $this->assertSame('Test Unit', $result['nome']);
        $this->assertSame('Test Description', $result['descricao']);
        $this->assertTrue($result['ativo']);
        $this->assertNull($result['timezone']);
        $this->assertSame('2025-08-01T10:00:00', $result['createdAt']);
        $this->assertSame('2025-08-02T12:00:00', $result['updatedAt']);
    }

    public function testJsonSerializeWithTimezone(): void
    {
        $unidade = (new Unidade())
            ->setId(1)
            ->setNome('Test Unit')
            ->setDescricao('Test Description')
            ->setAtivo(true)
            ->setTimezone('America/Sao_Paulo')
            ->setCreatedAt(new DateTimeImmutable('2025-08-01 10:00:00'))
            ->setUpdatedAt(new DateTimeImmutable('2025-08-02 12:00:00'));

        $result = $unidade->jsonSerialize();

        $this->assertSame(1, $result['id']);
        $this->assertSame('Test Unit', $result['nome']);
        $this->assertSame('Test Description', $result['descricao']);
        $this->assertTrue($result['ativo']);
        $this->assertSame('America/Sao_Paulo', $result['timezone']);
        $this->assertSame('2025-08-01T10:00:00', $result['createdAt']);
        $this->assertSame('2025-08-02T12:00:00', $result['updatedAt']);
    }

    public function testGetSetTimezone(): void
    {
        $unidade = new Unidade();

        $this->assertNull($unidade->getTimezone());

        $unidade->setTimezone('Europe/London');
        $this->assertSame('Europe/London', $unidade->getTimezone());

        $unidade->setTimezone('Asia/Tokyo');
        $this->assertSame('Asia/Tokyo', $unidade->getTimezone());

        $unidade->setTimezone(null);
        $this->assertNull($unidade->getTimezone());
    }
}
