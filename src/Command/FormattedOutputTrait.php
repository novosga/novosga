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

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * FormattedOutputTrait.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
trait FormattedOutputTrait
{
    protected function writef(OutputInterface $output, $message, $type)
    {
        /* @var $formatter FormatterHelper */
        $formatter = $this->getHelper('formatter');
        $formattedBlock = $formatter->formatBlock($message, $type, true);
        $output->writeln($formattedBlock);
    }
}
