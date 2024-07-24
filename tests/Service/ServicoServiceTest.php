<?php

namespace App\Tests\Service;

use App\Repository\ServicoMetadataRepository;
use App\Repository\ServicoRepository;
use App\Service\ServicoService;
use Novosga\Infrastructure\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServicoServiceTest extends TestCase
{
    private StorageInterface&MockObject $storage;
    private ServicoRepository&MockObject $servicoRepository;
    private ServicoMetadataRepository&MockObject $servicoMetadataRepository;

    private ServicoService $service;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->servicoRepository = $this->createMock(ServicoRepository::class);
        $this->servicoMetadataRepository = $this->createMock(ServicoMetadataRepository::class);

        $this->service = new ServicoService(
            $this->storage,
            $this->servicoRepository,
            $this->servicoMetadataRepository,
        );
    }

    public function testNovaSigla(): void
    {
        $this->assertSame('', $this->service->gerarSigla(0));
        $this->assertSame('A', $this->service->gerarSigla(1));
        $this->assertSame('Z', $this->service->gerarSigla(26));

        // single char
        $chars = range('A', 'Z');
        for ($i = 0; $i < count($chars); $i++) {
            $this->assertSame($chars[$i], $this->service->gerarSigla($i + 1));
        }

        // two chars
        $this->assertSame('AA', $this->service->gerarSigla(27));
        $this->assertSame('AE', $this->service->gerarSigla(31));
        $this->assertSame('AW', $this->service->gerarSigla(49));
        $this->assertSame('GR', $this->service->gerarSigla(200));

        // three chars
        $this->assertSame('ALL', $this->service->gerarSigla(1000));
        $this->assertSame('FRS', $this->service->gerarSigla(4543));
        $this->assertSame('NTO', $this->service->gerarSigla(9999));
    }
}
