<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * CheckCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class CheckCommand extends Command
{
    use FormattedOutputTrait;

    protected function configure()
    {
        $this->setName('novosga:check')
            ->setDescription('Check NovoSGA installation.')
            ->addOption('no-header', '', InputOption::VALUE_NONE, 'Disable comment header');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $showHeader = !$input->getOption('no-header');

        if ($showHeader) {
            $header = [
                "*******************\n",
                "Checking NovoSGA installation\n",
                "*******************",
            ];
            $this->writef($output, $header, 'info');
        }

        $vars = [
            'DATABASE_URL',
        ];

        foreach ($vars as $var) {
            $success = $this->checkEnvVar($output, $var);
            if (!$success) {
                return 1;
            }
        }

        return 0;
    }

    private function checkEnvVar(OutputInterface $output, $varname): bool
    {
        $var = getenv($varname);

        if (!$var) {
            $error = "Environment variable {$varname} not found.";
            $instruction = [
                "Please fill the missing variable in the .env file for development installation",
                "or set the variable on your environment for production stage.",
            ];

            $this->writef($output, $error, 'error');
            $this->writef($output, $instruction, 'comment');

            return false;
        }

        return true;
    }
}
