<?php
/**
 * Esse arquivo foi criado para agilizar o carregamento dos recursos dos módulos.
 * Quando uma requisição a um recurso do módulo é detectada pelo .htaccess ela é
 * redireciona para esse arquivo poder tratar, evitando verificar usuário, acesso, 
 * abrir conexão com o banco, etc.
 */
require_once  '../bootstrap.php';

use \Novosga\App;

$app = new App();

$app->any('/modules/:moduleKey/resources/:resource+', function($moduleKey, $resource) use ($app) {
    $app->moduleResource($moduleKey, join(DS, $resource));
});

$app->run();
