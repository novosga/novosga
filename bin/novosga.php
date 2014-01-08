#!/usr/bin/env php
<?php
require __DIR__ . '/../bootstrap.php';

use Novosga\Console\ResetCommand;
use Novosga\Console\UnidadesCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new ResetCommand($db->createEntityManager()));
$application->add(new UnidadesCommand($db->createEntityManager()));
$application->run();