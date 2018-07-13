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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UpdateCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UpdateCommand extends ContainerAwareCommand
{
    use FormattedOutputTrait;
    
    protected function configure()
    {
        $this
            ->setName('novosga:update')
            ->setDescription('Update command runned after composer update.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $this->getContainer()->getParameter('version');
        $header = [
            "*******************\n",
            "Updating NovoSGA v{$version} installation\n",
            "*******************",
        ];
        
        $this->writef($output, $header, 'info');
        
        $this->updateSchema($output);
    }
    
    protected function updateSchema(OutputInterface $output): bool
    {
        $updateDatabase = $this->getApplication()->find('doctrine:schema:update');
        $code = $updateDatabase->run(
            new ArrayInput([ '--force' => true ]),
            $output
        );
        
        return $code === 0;
    }
}
