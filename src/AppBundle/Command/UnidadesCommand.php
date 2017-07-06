<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Command;

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
            $unidades = $this->em->getRepository('Novosga\Entity\Unidade')->findBy(['ativo' => true], ['id' => 'ASC']);
            $output->writeln('<info>Unidades</info>');
            foreach ($unidades as $unidade) {
                $output->writeln("Id: {$unidade->getId()}, Unidade: {$unidade->getCodigo()} - {$unidade->getNome()}");
            }
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
