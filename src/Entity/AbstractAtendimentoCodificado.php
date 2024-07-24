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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\AtendimentoCodificadoInterface;
use Novosga\Entity\ServicoInterface;

/**
 * AbstractAtendimentoCodificado
 * atendimento codificado (servico realizado).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\MappedSuperclass]
abstract class AbstractAtendimentoCodificado implements AtendimentoCodificadoInterface
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Servico::class)]
    protected ?ServicoInterface $servico = null;

    #[ORM\Column(name: 'valor_peso', type: Types::SMALLINT)]
    protected ?int $peso;

    public function getServico(): ?ServicoInterface
    {
        return $this->servico;
    }

    public function setServico(?ServicoInterface $servico): static
    {
        $this->servico = $servico;

        return $this;
    }

    public function getPeso(): ?int
    {
        return $this->peso;
    }

    public function setPeso(?int $peso): static
    {
        $this->peso = $peso;

        return $this;
    }
}
