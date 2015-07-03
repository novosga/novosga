<?php

namespace Novosga\Console;

use Doctrine\ORM\EntityManager;
use Exception;
use Novosga\Service\ModuloService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ModuleRemoveCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModuleRemoveCommand extends Command
{
    private $em;

    public function __construct(EntityManager $em, $name = null)
    {
        parent::__construct($name = null);
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('module:remove')
            ->setDescription('Remove um módulo já instalado.')
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Chave do módulo a ser removido'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $key = $input->getArgument('key');
            $service = new ModuloService($this->em);
            $service->uninstall($key);
            $output->writeln('<info>Módulo desinstalado com sucesso</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
