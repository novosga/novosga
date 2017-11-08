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

use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * WebsocketCommand
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class WebsocketCommand extends ContainerAwareCommand
{
    private $validOptions = ['start', 'stop', 'restart', 'reload', 'status', 'connections'];
    
    protected function configure()
    {
        $this->setName('novosga:websocket')
            ->setDescription('Start/stop websocket server')
            ->addArgument('option', InputArgument::REQUIRED, implode('|', $this->validOptions));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $option      = $input->getArgument('option');
        
        if (!in_array($option, $this->validOptions)) {
            throw new Exception('Invalid option.');
        }
        
        $projectDir  = $this->getContainer()->getParameter('kernel.project_dir');
        $commandline = "{$projectDir}/vendor/novosga/websocket-server/bin/server {$option}";
        $process     = new Process($commandline);
        
        $process->start();
        $process->wait();
        
        $output->writeln($process->getOutput());
    }
}
