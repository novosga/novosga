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
use Novosga\Entity\AtendimentoInterface;

/**
 * Classe Atendimento Codificado
 * representa o atendimento codificado (servico realizado).
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'atendimentos_codificados')]
class AtendimentoCodificado extends AbstractAtendimentoCodificado
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Atendimento::class, inversedBy: 'codificados')]
    private ?AtendimentoInterface $atendimento = null;

    public function getAtendimento(): ?AtendimentoInterface
    {
        return $this->atendimento;
    }

    public function setAtendimento(?AtendimentoInterface $atendimento): static
    {
        $this->atendimento = $atendimento;

        return $this;
    }
}
