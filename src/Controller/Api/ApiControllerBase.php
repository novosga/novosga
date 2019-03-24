<?php

/*
 * This file is part of the Novo SGA project.
 *
 * (c) Rogerio Lino <rogeriolino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\Expression\ExpressionEvaluator;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * ApiControllerBase
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiControllerBase extends AbstractController
{
    /**
     * @var string
     */
    private $rootDir;
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    public function __construct(TranslatorInterface $translator, $rootDir)
    {
        $this->rootDir    = $rootDir;
        $this->translator = $translator;
    }
    
    /**
     * @return ObjectManager
     */
    protected function getManager()
    {
        $manager = $this
            ->getDoctrine()
            ->getManager();
        
        return $manager;
    }
    
    /**
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        $serializer =
            SerializerBuilder::create()
                ->addDefaultHandlers()
                ->setExpressionEvaluator(new ExpressionEvaluator(new ExpressionLanguage()))
                ->addMetadataDir("{$this->rootDir}/config/serializer/app", 'App')
                ->addMetadataDir("{$this->rootDir}/config/serializer/core", 'Novosga')
                ->build();
        
        return $serializer;
    }
    
    /**
     * @return TranslatorInterface
     */
    protected function getTranslator()
    {
        return $this->translator;
    }
    
    /**
     *
     * @param string $msg
     * @return string
     */
    protected function translate($id, array $params = [])
    {
        $translated = $this->getTranslator()->trans($id, $params);
        
        return $translated;
    }
}
