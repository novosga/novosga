<?php

namespace Novosga\Console;

use Doctrine\ORM\EntityManager;
use Exception;
use Novosga\Service\AtendimentoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ResetCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class ResetCommand extends Command
{
    private $em;

    public function __construct(EntityManager $em, $name = null)
    {
        parent::__construct($name = null);
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('reset')
            ->setDescription('Reinicia a numeração das senhas de todas ou uma única unidade.')
            ->addArgument(
                'unidade',
                InputArgument::OPTIONAL,
                'Id da unidade a ser reiniciada'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $id = (int) $input->getArgument('unidade');
            if ($id > 0) {
                // verificando unidade
                $unidade = $this->em->find('Novosga\Model\Unidade', $id);
                if (!$unidade) {
                    throw new Exception("Unidade inválida: $id");
                }
            }
            $service = new AtendimentoService($this->em);
            $service->acumularAtendimentos($id);
            $output->writeln('<info>Senhas reiniciadas com sucesso</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
