<?php
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    echo '<h1>Novo SGA</h1>
    <p>Por favor execute <strong>composer.phar install</strong> primeiro.</p>
    <p>Please run <strong>composer.phar install</strong> fisrt.</p>';
    exit();
}

define("DS", DIRECTORY_SEPARATOR);
define("NOVOSGA_DEV", false);
define("NOVOSGA_ROOT", __DIR__);
define("VENDOR_DIR", __DIR__ . DS . 'vendor');
define("NOVOSGA_CONFIG", NOVOSGA_ROOT . DS . 'config');
define("NOVOSGA_LOG", NOVOSGA_ROOT . DS . 'var/log');
define("NOVOSGA_CACHE", NOVOSGA_ROOT . DS . 'var/cache');
define("NOVOSGA_PUBLIC", NOVOSGA_ROOT . DS . 'public');
define("NOVOSGA_TEMPLATES", NOVOSGA_ROOT . DS . 'templates');
define("NOVOSGA_LOCALE_DIR", NOVOSGA_ROOT . DS . "locales");
define("MODULES_DIR", "modules");
define("MODULES_PATH", NOVOSGA_ROOT . DS . MODULES_DIR);

$loader = require $autoload;
