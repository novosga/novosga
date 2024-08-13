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

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * FormattedOutputTrait.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
trait FormattedOutputTrait
{
    /** @param string|iterable<string> $message */
    protected function writef(OutputInterface $output, string|iterable $message, string $type): void
    {
        /** @var FormatterHelper */
        $formatter = $this->getHelper('formatter');
        $formattedBlock = $formatter->formatBlock($message, $type, true);
        $output->writeln($formattedBlock);
    }
}
