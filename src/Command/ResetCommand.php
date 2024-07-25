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
use Exception;
use Doctrine\ORM\EntityManagerInterface;
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
        $seguro = $input->getOption('seguro');
        $unidade = null;

        if ($id > 0) {
            // verificando unidade
            $unidade = $this->em->find(Unidade::class, $id);
            if (!$unidade) {
                throw new Exception("Unidade inválida: $id");
            }
        }

        $ctx = [];

        if ($seguro) {
            $dt = new \DateTime();
            $dt->sub(new \DateInterval('P1D'));
            $dt->setTime(23, 59, 59);
            $ctx['data'] = $dt;
        }

        $this->atendimentoService->acumularAtendimentos($unidade, $ctx);

        $io = new SymfonyStyle($input, $output);
        $io->success('Senhas reiniciadas com sucesso');

        return self::SUCCESS;
    }
}
