<?php

namespace Novosga\Console;

use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UnidadesCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class UnidadesCommand extends Command
{
    private $em;

    public function __construct(EntityManager $em, $name = null)
    {
        parent::__construct($name = null);
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('unidades')
            ->setDescription('Lista as unidades do sistema e seus respectivos ids.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $unidades = $this->em->getRepository('Novosga\Model\Unidade')->findBy(array('status' => 1), array('id' => 'ASC'));
            $output->writeln('<info>Unidades</info>');
            foreach ($unidades as $unidade) {
                $output->writeln("Id: {$unidade->getId()}, Unidade: {$unidade->getCodigo()} - {$unidade->getNome()}");
            }
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
