<?php
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    echo '<h1>Novo SGA</h1>
    <p>Por favor execute <strong>composer.phar install</strong> primeiro.</p>
    <p>Please run <strong>composer.phar install</strong> fisrt.</p>';
    exit();
}

define("DS", DIRECTORY_SEPARATOR);
define("NOVOSGA_ROOT", __DIR__);
define("VENDOR_DIR", __DIR__ . DS . 'vendor');
define("NOVOSGA_CONFIG", NOVOSGA_ROOT . DS . 'config');
define("NOVOSGA_PUBLIC", NOVOSGA_ROOT . DS . 'public');
define("NOVOSGA_LOCALE_DIR", NOVOSGA_ROOT . DS . "locale");
define("MODULES_DIR", "modules");
define("MODULES_PATH", NOVOSGA_PUBLIC . DS . MODULES_DIR);

require $autoload;

// i18n
\Novosga\Util\I18n::bind();

$db = new Novosga\Db\DatabaseConfig(NOVOSGA_ROOT . '/config/database.php');

define("NOVOSGA_INSTALLED", $db->isIntalled());
if (NOVOSGA_INSTALLED) {
    $tipoNumeracao = \Novosga\Model\Configuracao::get($db->createEntityManager(), \Novosga\Model\Util\Senha::TIPO_NUMERACAO);
    define("NOVOSGA_TIPO_NUMERACAO", $tipoNumeracao ? $tipoNumeracao->getValor() : \Novosga\Model\Util\Senha::NUMERACAO_UNICA);
}
