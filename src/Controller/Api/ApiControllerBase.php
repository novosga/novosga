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

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ApiControllerBase
 *
 * @author Rogério Lino <rogeriolino@gmail.com>
 */
abstract class ApiControllerBase extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /** @param array<string,mixed> $params */
    protected function translate(string $id, array $params = []): string
    {
        $translated = $this->getTranslator()->trans($id, $params);

        return $translated;
    }
}
