<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository\ORM;

use Doctrine\ORM\EntityRepository;
use Novosga\Entity\Configuracao;
use Novosga\Repository\ConfiguracaoRepositoryInterface;

/**
 * ConfiguracaoRepository
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
class ConfiguracaoRepository extends EntityRepository implements ConfiguracaoRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(string $namespace, string $name)
    {
        return $this->findOneBy([
            'namespace' => $namespace,
            'name'      => $name,
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function set(string $namespace, string $name, $value): Configuracao
    {
        $em     = $this->getEntityManager();
        $config = $this->get($namespace, $name);
        
        if ($config instanceof Configuracao) {
            $config->setValue($value);
            $em->merge($config);
        } else {
            $config = new Configuracao();
            $config->setNamespace($namespace);
            $config->setName($name);
            $config->setValue($value);
            $em->persist($config);
        }
        
        $em->flush();
        
        return $config;
    }
}
