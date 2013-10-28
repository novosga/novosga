<?php
require_once  '../bootstrap.php';

use \Novosga\SGA;

$app = new SGA(array(
    'debug' => NOVOSGA_DEV,
    'cache' => NOVOSGA_CACHE,
    'db' => $db
));

$app->notFound(function() use ($app) {
    $app->render('error/404.html.twig');
});

$app->error(function(\Exception $e) use ($app) {
    $app->view()->set('exception', $e);
    $app->render('error/500.html.twig');
});

$app->get('/login', function() use ($app) {
    $ctrl = new \Novosga\Controller\LoginController($app);
    $ctrl->index($app->getContext());
    echo $app->render('login.html.twig');
});

$app->post('/login', function() use ($app) {
    $ctrl = new \Novosga\Controller\LoginController($app);
    $ctrl->validate($app->getContext());
});

$app->get('/logout', function() use ($app) {
    $app->getContext()->session()->destroy();
    $app->redirect($app->request()->getRootUri() . '/login');
});

$app->get('/install(/:page)', function($page = '') use ($app) {
    $controller = new \Novosga\Install\InstallController();
    if ($page === 'info') {
        $controller->info($app->getContext());
        exit();
    }
    $step = (int) $page;
    $data = $controller->doStep($app->getContext(), $step);
    $app->view()->appendData($data);
    $app->view()->set('context', $app->getContext());
    echo $app->render("install/step{$step}.html.twig");
});

$app->post('/install/:action', function($action) use ($app) {
    $controller = new \Novosga\Install\InstallController();
    $ref = new \ReflectionMethod($controller, $action);
    if ($ref->isPublic()) {
        $ref->invokeArgs($controller, array($app->getContext()));
    }
});


$app->any('/cron(/:action)', function($action = '') use ($app) {
    $controller = new \Novosga\Controller\CronController($app);
    $ref = new \ReflectionMethod($controller, $action);
    if ($ref->isPublic()) {
        $ref->invokeArgs($controller, array($app->getContext()));
    }
});

$app->get('/(home)', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->index($app->getContext());
    echo $app->render('home.html.twig');
});

$app->post('/home/set_unidade', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->unidade($app->getContext());
});

$app->get('/profile', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->perfil($app->getContext());
    echo $app->render('profile.html.twig');
});

$app->post('/profile', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->perfil($app->getContext());
    echo $app->render('profile.html.twig');
});

$app->post('/profile/password', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->alterar_senha($app->getContext());
    echo $app->render('profile.html.twig');
});

$app->any('/modules/:moduleKey(/:action+)', function($moduleKey, $action = 'index') use ($app) {
    define('MODULE', $moduleKey);
    if (!$app->getAcessoBusiness()->checkAccess($app->getContext(), $moduleKey, $action)) {
        $app->redirect($app->request()->getRootUri() . '/home');
    }
    $args = array($app->getContext());
    if (is_array($action)) {
        $args = array_merge($args, array_slice($action, 1));
        $action = $action[0];
    }
    // prefixo do nome do controlador do modulo
    $tokens = explode('.', $moduleKey);
    $namespace = MODULES_DIR . '\\' . $tokens[0] . '\\' . $tokens[1];
    $ctrlClassPrefix = $tokens[1];
    
    // buscando modulo a partir do banco
    $module = $app->getContext()->getModulo();
    
    // nome do controlador
    $ctrlClass = ucfirst($ctrlClassPrefix) . 'Controller';
    $ctrlClass = '\\' . $namespace . '\\' . $ctrlClass;
    $ctrl = new $ctrlClass($app, $module);
    
    $app->view()->twigTemplateDirs = array(
        NOVOSGA_TEMPLATES,
        MODULES_PATH . "/{$tokens[0]}/{$tokens[1]}/view"
    );
    $app->view()->set('module', $module);
    
    // controller action
    $methodName = str_replace('/', '_', str_replace('-', '_', $action));
    $method = new \ReflectionMethod($ctrl, $methodName);
    $method->invokeArgs($ctrl, $args);
    
    echo $app->render("$action.html.twig");
});

/*
 * API
 */
$app->any('/api(/:action(/:params+))', function($action = '', $params = array()) use ($app) {
    if (empty($action)) {
        $app->notFound();
    }
    $em = $app->getContext()->database()->createEntityManager();
    $api = new \Novosga\Api\ApiV1($em);
    // api action
    $methodName = str_replace('/', '_', str_replace('-', '_', $action));
    $method = new \ReflectionMethod($api, $methodName);
    $rs = $method->invokeArgs($api, $params);
    
    header('Content-type: application/json');
    echo json_encode($rs);
    exit();
});

$app->run();
