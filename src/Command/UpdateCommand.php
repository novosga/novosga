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

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * UpdateCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsCommand(name: 'novosga:update')]
class UpdateCommand extends Command
{
    use FormattedOutputTrait;

    public function __construct(
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update command runned after composer update.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = $this->params->get('version');
        $header = [
            "*******************",
            "Updating NovoSGA v{$version} installation",
            "*******************",
        ];

        $this->writef($output, $header, 'info');

        $this->runMigrations($output);

        return self::SUCCESS;
    }

    private function runMigrations(OutputInterface $output): bool
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $migration = $this->getApplication()->find('doctrine:migrations:migrate');
        $code = $migration->run($input, $output);

        return $code === 0;
    }
}
