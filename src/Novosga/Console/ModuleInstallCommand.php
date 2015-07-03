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
 * ModuleInstallCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ModuleInstallCommand extends Command
{
    private $em;

    public function __construct(EntityManager $em, $name = null)
    {
        parent::__construct($name = null);
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('module:install')
            ->setDescription('Instala um novo módulo.')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Diretório ou arquivo zip do módulo'
            )
            ->addArgument(
                'key',
                InputArgument::OPTIONAL,
                'Chave do módulo quando instalando via diretório'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $filename = $input->getArgument('filename');
            $service = new ModuloService($this->em);
            if (is_dir($filename)) {
                $key = $input->getArgument('key');
                if (empty($key)) {
                    throw new Exception('Ao instalar a partir de um diretório, deve especificar a chave do módulo');
                }
                $service->install($filename, $key);
            } else {
                $service->extractAndInstall($filename);
            }
            $output->writeln('<info>Módulo instalado com sucesso</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
