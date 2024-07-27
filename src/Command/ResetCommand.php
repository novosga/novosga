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

use App\Entity\Unidade;
use App\Service\AtendimentoService;
use DateInterval;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * ResetCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsCommand(name: 'novosga:reset')]
class ResetCommand extends Command
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly EntityManagerInterface $em,
        private readonly AtendimentoService $atendimentoService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Reinicia a numeração das senhas de todas ou uma única unidade.')
            ->addArgument(
                'unidade',
                InputArgument::OPTIONAL,
                'Id da unidade a ser reiniciada'
            )
            ->addOption(
                'seguro',
                's',
                InputOption::VALUE_NONE,
                'Se for informado não apagará os atendimentos do dia atual'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int) $input->getArgument('unidade');
        $seguro = (bool) $input->getOption('seguro');
        $unidade = null;

        if ($id > 0) {
            // verificando unidade
            $unidade = $this->em->find(Unidade::class, $id);
            if (!$unidade) {
                throw new Exception("Unidade inválida: $id");
            }
        }

        $ateData = $this->clock->now();

        if ($seguro) {
            $ateData = $ateData
                ->sub(new DateInterval('P1D'))
                ->setTime(23, 59, 59);
        }

        $this->atendimentoService->acumularAtendimentos(null, $unidade, $ateData);

        $io = new SymfonyStyle($input, $output);
        $io->success('Senhas reiniciadas com sucesso');

        return self::SUCCESS;
    }
}
