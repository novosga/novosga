#!/usr/bin/env php
<?php
require __DIR__ . '/../bootstrap.php';

use Novosga\Console as Cmd;
use Symfony\Component\Console\Application;

$db = Novosga\Config\DatabaseConfig::getInstance();

$application = new Application();
$application->add(new Cmd\ResetCommand($db->createEntityManager()));
$application->add(new Cmd\UnidadesCommand($db->createEntityManager()));
$application->add(new Cmd\ModuleInstallCommand($db->createEntityManager()));
$application->add(new Cmd\ModuleRemoveCommand($db->createEntityManager()));
$application->run();