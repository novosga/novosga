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

use App\Entity\Local;
use App\Entity\Prioridade;
use App\Entity\Unidade;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * InstallCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
#[AsCommand(name: 'novosga:install')]
class InstallCommand extends UpdateCommand
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct($params);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Install command runned after composer install.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = $this->params->get('version');
        $header = [
            "*******************",
            "Welcome to NovoSGA v{$version} installer",
            "*******************",
        ];

        $this->writef($output, $header, 'info');

        $output->writeln('> Checking environment...');

        if (!$this->checkEnv($output)) {
            return 1;
        }

        $output->writeln('Environment <info>Ok</info>!');

        $output->writeln('> Creating database...');

        if (!$this->createDatabase($output)) {
            return 1;
        }

        $output->writeln('Database <info>Ok</info>!');

        $output->writeln('> Running database migrations...');

        if (!$this->runMigrations($output)) {
            return 1;
        }

        $output->writeln('Migrations <info>Ok</info>!');

        $output->writeln('> Checking data...');

        // user
        if (!$this->existsData(Usuario::class)) {
            $username = $this->read(
                $input,
                $output,
                'NOVOSGA_ADMIN_USERNAME',
                '[Admin] Please enter the username of administrator user: ',
                'admin'
            );
            $password = $this->read(
                $input,
                $output,
                'NOVOSGA_ADMIN_PASSWORD',
                '[Admin] Please enter the administrator password: ',
                hidden: true,
            );
            if (strlen($password) < 6) {
                throw new Exception('The admin password must contain at least 6 characters');
            }
            $firstname = $this->read(
                $input,
                $output,
                'NOVOSGA_ADMIN_FIRSTNAME',
                '[Admin] Please enter the firstname of administrator user: ',
                'Administrator'
            );
            $lastname = $this->read(
                $input,
                $output,
                'NOVOSGA_ADMIN_LASTNAME',
                '[Admin] Please enter the lastname of administrator user: ',
                'Global'
            );

            $admin = $this->createAdmin($firstname, $lastname, $username, $password);
            $this->em->persist($admin);
        }

        // unity
        if (!$this->existsData(Unidade::class)) {
            $unityName = $this->read(
                $input,
                $output,
                'NOVOSGA_UNITY_NAME',
                '[Unity] Unity name: ',
                'Unidade padrão'
            );
            $unityDescription = $this->read(
                $input,
                $output,
                'NOVOSGA_UNITY_DESCRIPTION',
                '[Unity] Unity description: ',
                'UNI1'
            );

            $unity = $this->createUnity($unityName, $unityDescription);
            $this->em->persist($unity);
        }

        // priority
        if (!$this->existsData(Prioridade::class)) {
            $p1Name = $this->read(
                $input,
                $output,
                'NOVOSGA_NOPRIORITY_NAME',
                '[No priority] No priority name: ',
                'Normal'
            );
            $p1Description = $this->read(
                $input,
                $output,
                'NOVOSGA_NOPRIORITY_DESCRIPTION',
                '[No priority] No priority description: ',
                'Sem prioridade'
            );

            $p2Name = $this->read(
                $input,
                $output,
                'NOVOSGA_PRIORITY_NAME',
                '[Priority] Priority name: ',
                'Prioridade'
            );
            $p2Description = $this->read(
                $input,
                $output,
                'NOVOSGA_PRIORITY_DESCRIPTION',
                '[Priority] Priority description: ',
                'Atendimento prioritário'
            );

            $noPriority = $this->createPriority($p1Name, $p1Description, 0);
            $priority   = $this->createPriority($p2Name, $p2Description, 1);

            $this->em->persist($noPriority);
            $this->em->persist($priority);
        }

        // attendance place
        if (!$this->existsData(Local::class)) {
            $placeName = $this->read(
                $input,
                $output,
                'NOVOSGA_PLACE_NAME',
                '[Place] Default attendance place name: ',
                'Guichê'
            );

            $place = $this->createPlace($placeName);
            $this->em->persist($place);
        }

        $this->em->flush();
        $output->writeln('Data <info>Ok</info>.');

        return self::SUCCESS;
    }

    private function checkEnv(OutputInterface $output): bool
    {
        $check = $this->getApplication()->find('novosga:check');
        $code = $check->run(
            new ArrayInput([ '--no-header' => true ]),
            $output
        );

        return $code === 0;
    }

    private function createDatabase(OutputInterface $output): bool
    {
        $createDatabase = $this->getApplication()->find('doctrine:database:create');
        $code = $createDatabase->run(
            new ArrayInput([ '--if-not-exists' => true ]),
            $output
        );

        return $code === 0;
    }

    private function runMigrations(OutputInterface $output): bool
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $migration = $this->getApplication()->find('doctrine:migrations:migrate');
        $code = $migration->run($input, $output);

        return $code === 0;
    }

    /**
     * @template T of object
     * @param class-string<T> $entityClass
     */
    private function existsData(string $entityClass): bool
    {
        /** @var EntityRepository<T> */
        $repo = $this->em->getRepository($entityClass);
        $entity = $repo->findOneBy([]);

        return $entity !== null;
    }

    private function read(
        InputInterface $input,
        OutputInterface $output,
        string $envname,
        string $message,
        mixed $default = null,
        bool $hidden = false,
    ): mixed {
        $envvar = $_ENV[$envname] ?? getenv($envname);
        if ($envvar) {
            return $envvar;
        }

        if ($default) {
            $message .= "[{$default}] ";
        }

        /** @var QuestionHelper */
        $helper = $this->getHelper('question');
        $question = new Question($message, $default);
        if ($hidden) {
            $question->setHidden(true);
            $question->setHiddenFallback(false);
        }
        $value = $helper->ask($input, $output, $question);

        return $value;
    }

    private function createAdmin(
        string $firstname,
        string $lastname,
        string $username,
        string $password
    ): Usuario {
        $user = (new Usuario())
            ->setNome($firstname)
            ->setSobrenome($lastname)
            ->setLogin($username)
            ->setAlgorithm('bcrypt')
            ->setSalt(null)
            ->setAdmin(true)
            ->setAtivo(true);

        $encoded = $this->passwordHasher->hashPassword($user, $password);

        $user->setSenha($encoded);

        return $user;
    }

    private function createUnity(string $name, string $description): Unidade
    {
        $unidade = new Unidade();
        $unidade->setNome($name);
        $unidade->setDescricao($description);
        $unidade->setAtivo(true);

        return $unidade;
    }

    private function createPriority(string $name, string $description, int $weight): Prioridade
    {
        $prioridade = new Prioridade();
        $prioridade->setNome($name);
        $prioridade->setDescricao($description);
        $prioridade->setPeso($weight);
        $prioridade->setAtivo(true);

        return $prioridade;
    }

    private function createPlace(string $name): Local
    {
        $local = new Local();
        $local->setNome($name);

        return $local;
    }
}
