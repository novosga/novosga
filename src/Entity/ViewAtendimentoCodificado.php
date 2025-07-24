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

namespace App\Entity;

use App\Repository\ViewAtendimentoCodificadoRepository;
use Doctrine\ORM\Mapping as ORM;
use Novosga\Entity\AtendimentoInterface;

/**
 * view Atendimento Codificado
 * União dos atendimentos atuais e do histórico
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[ORM\Entity(readOnly: true, repositoryClass: ViewAtendimentoCodificadoRepository::class)]
#[ORM\Table(name: 'view_atendimentos_codificados')]
class ViewAtendimentoCodificado extends AbstractAtendimentoCodificado
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ViewAtendimento::class, inversedBy: 'codificados')]
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
