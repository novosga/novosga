<?php
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    echo '<h1>Novo SGA</h1><p>Please run <strong>composer.phar update</strong> fisrt</p>';
    exit();
}
require $autoload;
require __DIR__ . '/lib/novosga/Constants.php';