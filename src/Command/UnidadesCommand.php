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

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UnidadesCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsCommand(name: 'novosga:unidades')]
class UnidadesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Lista as unidades do sistema e seus respectivos ids.')
            ->addOption('json', null, null, 'Retorna as unidades no formato JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $unidades = $this
            ->em
            ->getRepository(\App\Entity\Unidade::class)
            ->findBy([], ['id' => 'ASC']);

        $json = $input->getOption('json');

        if ($json) {
            $arr = [];
            foreach ($unidades as $unidade) {
                $arr[] = [
                    'id'        => $unidade->getId(),
                    'nome'      => $unidade->getNome(),
                    'descricao' => $unidade->getDescricao(),
                    'ativo'     => $unidade->isAtivo(),
                ];
            }
            $output->writeln(json_encode($arr));
        } else {
            $output->writeln('<info>Unidades</info>');

            foreach ($unidades as $unidade) {
                $ativo = $unidade->isAtivo() ? 'Sim' : 'Não';

                $output->writeln("----------");
                $output->writeln("Id:        {$unidade->getId()}");
                $output->writeln("Nome:      {$unidade->getNome()}");
                $output->writeln("Descrição: {$unidade->getDescricao()}");
                $output->writeln("Ativo:     {$ativo}");
            }
        }

        return self::SUCCESS;
    }
}
