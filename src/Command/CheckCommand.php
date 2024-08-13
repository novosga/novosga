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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Dotenv\Dotenv;

/**
 * CheckCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsCommand(name: 'novosga:check')]
class CheckCommand extends Command
{
    use FormattedOutputTrait;

    protected function configure(): void
    {
        $this
            ->setDescription('Check NovoSGA installation.')
            ->addOption('no-header', '', InputOption::VALUE_NONE, 'Disable comment header');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        (new Dotenv())->bootEnv(dirname(dirname(__DIR__)) . '/.env');
        $showHeader = !$input->getOption('no-header');

        if ($showHeader) {
            $header = [
                "*******************",
                "Checking NovoSGA installation",
                "*******************",
            ];
            $this->writef($output, $header, 'info');
        }

        $vars = [
            'APP_LANGUAGE',
            'DATABASE_URL',
            'MERCURE_URL',
            'MERCURE_PUBLIC_URL',
            'MERCURE_JWT_SECRET',
            'OAUTH_PRIVATE_KEY',
            'OAUTH_PUBLIC_KEY',
            'OAUTH_PASSPHRASE',
            'OAUTH_ENCRYPTION_KEY',
        ];

        foreach ($vars as $var) {
            $this->checkEnvVar($output, $var);
        }

        return self::SUCCESS;
    }

    private function checkEnvVar(OutputInterface $output, string $varname): void
    {
        $output->write(sprintf('> Checking environment variable %s ...', $varname));
        if ($this->hasVariable($varname)) {
            $output->writeln(" [OK]");
        } else {
            $output->writeln(" [ERR]");

            $error = "Environment variable {$varname} not found.";
            $instruction = [
                "Please fill the missing variable in the .env file for development installation",
                "or set the variable on your environment for production stage.",
            ];

            $this->writef($output, $error, 'error');
            $this->writef($output, $instruction, 'comment');
        }
    }

    private function hasVariable(string $varname): bool
    {
        return isset($_ENV[$varname]) || !!getenv($varname);
    }
}
