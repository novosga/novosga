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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * UpdateCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UpdateCommand extends Command
{
    use FormattedOutputTrait;

    protected static $defaultName = 'novosga:update';

    protected $params;

    public function __construct(ParameterBagInterface $params)
    {
        parent::__construct();
        $this->params = $params;
    }
    
    protected function configure()
    {
        $this
            ->setDescription('Update command runned after composer update.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $this->params->get('version');
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
