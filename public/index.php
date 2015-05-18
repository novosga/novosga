<?php
require_once  '../bootstrap.php';

$app = Novosga\App::create();
$app->prepare();

$app->get('/login', function() use ($app) {
    $ctrl = new \Novosga\Controller\LoginController($app);
    $ctrl->index($app->getContext());
    echo $app->render('login.html.twig');
})->name('login');

$app->post('/login', function() use ($app) {
    $ctrl = new \Novosga\Controller\LoginController($app);
    $ctrl->validate($app->getContext());
});

$app->get('/logout', function() use ($app) {
    $app->getContext()->session()->destroy();
    $app->gotoLogin();
})->name('logout');

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
})->name('install');

$app->post('/install/:action', function($action) use ($app) {
    $controller = new \Novosga\Install\InstallController();
    $ref = new \ReflectionMethod($controller, $action);
    if ($ref->isPublic()) {
        $response = $ref->invokeArgs($controller, array($app->getContext()));
        if ($response && $response instanceof \Novosga\Http\JsonResponse) {
            echo $response->toJson();
        }
    }
});

$app->get('/(home)', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->index($app->getContext());
    echo $app->render('home.html.twig');
})->name('home');

$app->post('/home/set_unidade', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $response = $ctrl->unidade($app->getContext());
    echo $response->toJson();
});

$app->any('/profile', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $ctrl->perfil($app->getContext());
    echo $app->render('profile.html.twig');
});

$app->post('/profile/password', function() use ($app) {
    $ctrl = new \Novosga\Controller\HomeController($app);
    $response = $ctrl->alterar_senha($app->getContext());
    echo $response->toJson();
});

$app->get('/print/:id/:hash', function($id, $hash) use ($app) {
    $ctrl = new \Novosga\Controller\TicketController($app);
    $template = $ctrl->printAction($app->getContext(), $id, $hash);
    echo $app->render($template);
});

$app->any('/modules/:moduleKey(/:action+)', function($moduleKey, $action = 'index') use ($app, $loader) {
    define('MODULE', $moduleKey);
    if (!$app->getAcessoService()->checkAccess($app->getContext(), $moduleKey, $action)) {
        $app->gotoHome();
    }
    $args = array($app->getContext());
    if (is_array($action)) {
        $args = array_merge($args, array_slice($action, 1));
        $action = $action[0];
    }
    // prefixo do nome do controlador do modulo
    $tokens = explode('.', $moduleKey);
    
    // module resource .htaccess fallback
    if ($action === 'resources') {
        try {
            $app->moduleResource($moduleKey, join(DS, array_slice($args, 1)));
        } catch (Exception $e) {
            $app->notFound();
        }
    }
    
    $namespace = MODULES_DIR . '\\' . $tokens[0] . '\\' . $tokens[1];
    $ctrlClassPrefix = $tokens[1];
    
    // buscando modulo a partir do banco
    $module = $app->getContext()->getModulo();
    
    // nome do controlador
    $ctrlClass = ucfirst($ctrlClassPrefix) . 'Controller';
    $ctrlClass = '\\' . $namespace . '\\' . $ctrlClass;
    $ctrl = new $ctrlClass($app, $module);
    
    $moduleDir = MODULES_PATH . "/{$tokens[0]}/{$tokens[1]}";
    // module locale
    \Novosga\Util\I18n::bindDomain($moduleKey, "$moduleDir/locales");
    // module views
    $app->view()->twigTemplateDirs = array(NOVOSGA_TEMPLATES, "$moduleDir/views");
    $app->view()->set('module', $module);
    
    // controller action
    $methodName = str_replace('/', '_', str_replace('-', '_', $action));
    $method = new \ReflectionMethod($ctrl, $methodName);
    $response = $method->invokeArgs($ctrl, $args);
    if ($response instanceof \Novosga\Http\JsonResponse) {
        $app->response()->header('Content-type', 'application/json');
        $app->response()->write($response->toJson());
    } else {
        // render as template the returned template name or action name pattern
        if (is_string($response)) {
            $template = $response;
        } else {
            $template = "$action.html.twig";
        }
        echo $app->render($template);
    }
});

$app->run();
