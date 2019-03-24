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

use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Novosga\Entity\Prioridade;
use Novosga\Entity\Unidade;
use Novosga\Entity\Usuario;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * InstallCommand.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class InstallCommand extends UpdateCommand
{
    protected static $defaultName = 'novosga:install';

    /**
     * @var ObjectManager
     */
    private $om;
    
    public function __construct(ObjectManager $om, ParameterBagInterface $params)
    {
        parent::__construct($params);
        $this->om = $om;
    }

    protected function configure()
    {
        $this
            ->setDescription('Install command runned after composer install.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $this->params->get('version');
        $header  = [
            "*******************\n",
            "Welcome to NovoSGA v{$version} installer\n",
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
        
        $output->writeln('> Updating database schema...');
        
        if (!$this->updateSchema($output)) {
            return 1;
        }
        
        $output->writeln('Schema <info>Ok</info>!');
        
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
                '[Admin] Please enter the administrator password: '
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
            $this->om->persist($admin);
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
            $this->om->persist($unity);
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
            
            $this->om->persist($noPriority);
            $this->om->persist($priority);
        }

        // attendance place
        if (!$this->existsData(\Novosga\Entity\Local::class)) {
            $placeName = $this->read(
                $input,
                $output,
                'NOVOSGA_PLACE_NAME',
                '[Place] Default attendance place name: ',
                'Guichê'
            );
            
            $place = $this->createPlace($placeName);
            $this->om->persist($place);
        }
            
        $this->om->flush();
        $output->writeln('Data <info>Ok</info>.');
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
        $code      = $migration->run($input, $output);
        
        return $code === 0;
    }
    
    private function existsData($entityClass)
    {
        $entity = $this
            ->om
            ->getRepository($entityClass)
            ->findOneBy([]);
        
        return !!$entity;
    }
    
    private function read(
        InputInterface $input,
        OutputInterface $output,
        string $envname,
        string $message,
        $default = null
    ) {
        $envvar = getenv($envname);
        
        if ($envvar) {
            return $envvar;
        }
        
        if ($default) {
            $message .= "[{$default}] ";
        }
        
        /* @var $helper QuestionHelper */
        $helper    = $this->getHelper('question');
        $question  = new Question($message, $default);
        $value     = $helper->ask($input, $output, $question);
        
        return $value;
    }
    
    private function createAdmin(
        string $firstname,
        string $lastname,
        string $username,
        string $password
    ): Usuario {
        $user = new Usuario();
        $user->setNome($firstname);
        $user->setSobrenome($lastname);
        $user->setLogin($username);
        $user->setAlgorithm('bcrypt');
        $user->setSalt(null);
        $user->setAdmin(true);
        $user->setAtivo(true);

        $encoder = new BCryptPasswordEncoder(12);
        $encoded = $encoder->encodePassword($password, $user->getSalt());

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
    
    private function createPlace(string $name): \Novosga\Entity\Local
    {
        $local = new \Novosga\Entity\Local();
        $local->setNome($name);
        
        return $local;
    }
}
