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

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\ConfiguracaoImpressaoInterface;

#[ORM\Embeddable]
class ConfiguracaoImpressao implements ConfiguracaoImpressaoInterface
{
    #[ORM\Column(length: 150)]
    private ?string $cabecalho = null;

    #[ORM\Column(length: 150)]
    private ?string $rodape = null;

    #[ORM\Column]
    private ?bool $exibirNomeServico = null;

    #[ORM\Column]
    private ?bool $exibirNomeUnidade = null;

    #[ORM\Column]
    private ?bool $exibirMensagemServico = null;

    #[ORM\Column]
    private ?bool $exibirData = null;

    #[ORM\Column]
    private ?bool $exibirPrioridade = null;

    public function __construct()
    {
        $this->cabecalho  = 'Novo SGA';
        $this->rodape     = 'Novo SGA';
        $this->exibirData = true;
        $this->exibirMensagemServico = true;
        $this->exibirNomeServico     = true;
        $this->exibirNomeUnidade     = true;
        $this->exibirPrioridade      = true;
    }

    public function getCabecalho(): ?string
    {
        return $this->cabecalho;
    }

    public function setCabecalho(?string $cabecalho): static
    {
        $this->cabecalho = $cabecalho;

        return $this;
    }

    public function getRodape(): ?string
    {
        return $this->rodape;
    }

    public function setRodape(?string $rodape): static
    {
        $this->rodape = $rodape;

        return $this;
    }

    public function getExibirNomeServico(): ?bool
    {
        return $this->exibirNomeServico;
    }

    public function setExibirNomeServico(?bool $exibirNomeServico): static
    {
        $this->exibirNomeServico = $exibirNomeServico;

        return $this;
    }

    public function getExibirNomeUnidade(): ?bool
    {
        return $this->exibirNomeUnidade;
    }

    public function setExibirNomeUnidade(?bool $exibirNomeUnidade): static
    {
        $this->exibirNomeUnidade = $exibirNomeUnidade;

        return $this;
    }

    public function getExibirMensagemServico(): ?bool
    {
        return $this->exibirMensagemServico;
    }

    public function setExibirMensagemServico(?bool $exibirMensagemServico): static
    {
        $this->exibirMensagemServico = $exibirMensagemServico;

        return $this;
    }

    public function getExibirData(): ?bool
    {
        return $this->exibirData;
    }

    public function setExibirData(?bool $exibirData): static
    {
        $this->exibirData = $exibirData;

        return $this;
    }

    public function getExibirPrioridade(): ?bool
    {
        return $this->exibirPrioridade;
    }

    public function setExibirPrioridade(?bool $exibirPrioridade): static
    {
        $this->exibirPrioridade = $exibirPrioridade;

        return $this;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'cabecalho'             => $this->getCabecalho(),
            'rodape'                => $this->getRodape(),
            'exibirData'            => $this->getExibirData(),
            'exibirPrioridade'      => $this->getExibirPrioridade(),
            'exibirNomeUnidade'     => $this->getExibirNomeUnidade(),
            'exibirNomeServico'     => $this->getExibirNomeServico(),
            'exibirMensagemServico' => $this->getExibirMensagemServico(),
        ];
    }
}
