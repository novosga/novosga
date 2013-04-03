<?php
ini_set('display_errors', 1);

require('core' . DIRECTORY_SEPARATOR . 'SGA.php');

use \core\SGA;
SGA::getContext();
use \core\Config;
use \core\util\Arrays;
use \core\business\AcessoBusiness;

// i18n
\core\util\I18n::bind();

// redirect to installer
if (!Config::SGA_INSTALLED && !isset($_GET[SGA::K_INSTALL])) {
    $lastStep = (int) SGA::getContext()->getSession()->getGlobal(SGA::K_INSTALL);
    $step = (int) Arrays::value($_GET, SGA::K_INSTALL, $lastStep);
    SGA::redirect('/' . SGA::K_INSTALL . '=' . $step);
    exit();
}

// home page
if (sizeof($_GET) == 0) {
    $_GET['home'] = 'index.php';
}

$context = SGA::getContext();
$context->setParameters($_GET);

foreach ($_GET as $key => $value) {
    if (empty($value)) {
        $value = 'index.php';
    }
    switch ($key) {
    case SGA::K_LOGIN:
    case SGA::K_LOGOUT:
    case SGA::K_HOME:
    case SGA::K_INSTALL:
    case SGA::K_PANEL:
    case SGA::K_CRON:
    case SGA::K_MODULE:
        if (AcessoBusiness::isProtectedPage($key)) {
            AcessoBusiness::checkAccess($key, $value);
        }
        // na instalacao usa sempre o index
        if ($key == SGA::K_INSTALL) {
            $value = 'index.php';
        }
        // definindo constante do modulo atual
        if ($key == SGA::K_MODULE) {
            // prefixo do nome do controlador do modulo
            $tokens = explode('.', $value);
            $namespace = MODULES_DIR . '\\' . $tokens[0] . '\\' . $tokens[1];
            $ctrlClassPrefix = $tokens[1];
        } else {
            // prefixo dos demais controladores
            $namespace = $key;
            $ctrlClassPrefix = $key;
        }
        // nome do controlador
        $ctrlClass = ucfirst($ctrlClassPrefix) . 'Controller';
        $ctrlClass = '\\' . $namespace . '\\' . $ctrlClass;
        $ctrl = new $ctrlClass();

        // controller action
        $page = Arrays::value($_GET, SGA::K_PAGE, 'index');
        $context->setParameter(SGA::K_PAGE, $page);
        $methodName = str_replace('/', '_', str_replace('-', '_', $page));
        $method = new \ReflectionMethod($ctrl, $methodName);
        $method->invokeArgs($ctrl, array($context));

        $html = $ctrl->view()->render($context);
        $context->getResponse()->updateHeaders();
        echo $html;
        exit();
    }
}
// nenhuma opcao, joga para o login
SGA::redirect('/' . SGA::K_LOGIN);
