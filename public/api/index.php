<?php
/*
 * Novo SGA API
 */
require_once  '../../bootstrap.php';

$app = new Slim\Slim(array(
    'debug' => false
));

$db = \Novosga\Config\DatabaseConfig::getInstance();
$em = $db->createEntityManager();
$server = new \Novosga\Api\OAuth2Server($em);

$api = new \Novosga\Api\ApiV1($em);

$app->error(function(Exception $e) use ($app) {
    echo json_encode(array('error' => $e->getMessage(), 'code' => $e->getCode()));
});

$app->notFound(function() use ($app) {
    echo json_encode(array('error' => 'Not found', 'code' => '404'));
});

/**
 * Autentica o usuário retornando o token de acesso.
 * 
 * POST /token
 * {
 *   "grant_type": "password",
 *   "username": "admin",
 *   "password": "123456",
 *   "client_id": "..."
 * }
 * < 200
 * {
 *   "access_token": "6cdcf2e7a7bbeac1bd76dcbf33cb59b9b7341613",
 *   "expires_in": 3600,
 *   "token_type": "Bearer",
 *   "scope": null,
 *   "refresh_token": "e4652c71918e1325f88a308f9e4401bcd62aa0c1"
 * }
 */
$app->post('/token', function() use ($server) {
    $server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
});

/**
 * Verifica o acesso, retornando se o token informado ainda está valido
 */
$app->get('/check', function() use ($server) {
    $server->checkAccess();
    echo json_encode(array('success' => true));
});

/**
 * Retorna todas as unidades disponíveis
 * 
 * GET /unidades
 * < 200
 * [
 *   {
 *     id: 1,
 *     codigo: "1",
 *     nome: "Unidade Padrão"
 *   }
 * ]
 */
$app->get('/unidades', function() use ($api) {
    echo json_encode($api->unidades());
});

/**
 * Retorna todas as prioridades disponíveis
 * 
 * GET /prioridades
 * < 200
 * [
 *   {
 *     id: 3,
 *     nome: "Gestante"
 *   },
 *   {
 *     id: 4,
 *     nome: "Idoso"
 *   }
 * ]
 */
$app->get('/prioridades', function() use ($api) {
    echo json_encode($api->prioridades());
});

/**
 * Retorna os serviços globais ou habilitados por unidade (quando a mesma for informada)
 * 
 * GET /servicos
 * < 200
 * [ 
 *   { 
 *     id: 1, 
 *     nome: "Serviço 1"
 *   },
 *   { 
 *     id: 2, 
 *     nome: "Serviço 2"
 *   }
 * ]
 * 
 * GET /servicos/1
 * < 200
 * [ 
 *   { 
 *     id: 1, 
 *     sigla: "A",
 *     nome: "Serviço 1",
 *     local: "Guichê"
 *   }
 * ]
 */
$app->get('/servicos(/:unidade)', function($unidade = 0) use ($api) {
    echo json_encode($api->servicos($unidade));
});

/**
 * Retorna a senhas a serem chamadas pelo painel da unidade. Uma lista de serviços
 * deve ser informada na query string (separados por vírgula)
 * 
 * GET /painel/1?servicos=1
 * < 200
 * [
 *   {
 *     id: 4,
 *     sigla: "A",
 *     mensagem: "Convencional",
 *     numero: 1,
 *     local: "Guichê",
 *     numeroLocal: 99,
 *     peso: 0,
 *     length: 3
 *   }
 * ]
 */
$app->get('/painel(/:unidade)', function($unidade = 0) use ($app, $api) {
    $servicos = $app->request()->get('servicos');
    if (empty($servicos)) {
        $servicos = 0;
    }
    // filtrando apenas inteiros (possiveis ids)
    $servicos = array_filter(explode(',', $servicos), function($value) {
        return $value > 0;
    });
    echo json_encode($api->painel($unidade, $servicos));
});

/**
 * Retorna a fila de atendimento do usuário na unidade informada.
 * 
 * GET /fila/usuario/1/1
 * < 200
 * [
 *   {
 *     id: 7,
 *     senha: "A002",
 *     servico: "Serviço 1",
 *     prioridade: true,
 *     nomePrioridade: "Gestante",
 *     chegada: "2014-04-23 08:41:00",
 *     espera: "00:18:22",
 *     numero: "A002",
 *     triagem: "admin",
 *     status: 1,
 *     nomeStatus: "Senha emitida",
 *     cliente: {
 *       nome: "",
 *       documento: ""
 *     }
 *   }
 * ]
 */
$app->get('/fila/usuario/:unidade/:usuario', function($unidade, $usuario) use ($app, $api) {
    $arr = array();
    $fila = $api->filaUsuario($unidade, $usuario);
    foreach ($fila as $a) {
        $arr[] = $a->jsonSerialize();
    }
    echo json_encode($arr);
});

/**
 * Retorna a fila de atendimento para os servicos da unidade. Uma lista de serviços
 * deve ser informada na query string (separados por vírgula)
 * 
 * GET /fila/servicos/1?servicos=1
 * < 200
 * [
 *   {
 *     id: 7,
 *     senha: "A002",
 *     servico: "Serviço 1",
 *     prioridade: true,
 *     nomePrioridade: "Gestante",
 *     chegada: "2014-04-23 08:41:00",
 *     espera: "00:18:22",
 *     numero: "A002",
 *     triagem: "admin",
 *     status: 1,
 *     nomeStatus: "Senha emitida",
 *     cliente: {
 *       nome: "",
 *       documento: ""
 *     }
 *   }
 * ]
 */
$app->get('/fila/servicos/:unidade', function($unidade) use ($app, $api) {
    $servicos = $app->request()->get('servicos');
    if (empty($servicos)) {
        $servicos = 0;
    }
    // filtrando apenas inteiros (possiveis ids)
    $servicos = array_filter(explode(',', $servicos), function($value) {
        return $value > 0;
    });
    $arr = array();
    $fila = $api->filaServicos($unidade, $servicos);
    foreach ($fila as $a) {
        $arr[] = $a->jsonSerialize();
    }
    echo json_encode($arr);
});

/**
 * Distribui uma nova senha para atendimento. Requer autenticação, um access_token válido e ativo.
 * 
 * POST /distribui
 * form data:
 *   int unidade (id da unidade do atendimento)
 *   int servico (id do serviço do atendimento)
 *   int prioridade (id da prioridade do atendimento, 1 para sem prioridade)
 *   string nome_cliente (nome do cliente a ser atendido)
 *   string doc_cliente (documento do cliente a ser atendido)
 */
$app->post('/distribui', function() use ($app, $api, $server) {
    $server->checkAccess();
    // authenticated user
    $usuario = $server->user();
    // post vars
    $unidade = (int) $app->request()->post('unidade');
    $servico = (int) $app->request()->post('servico');
    $prioridade = (int) $app->request()->post('prioridade');
    $nomeCliente = $app->request()->post('nome_cliente');
    $documentoCliente = $app->request()->post('doc_cliente');
    // distribuindo nova senha
    echo json_encode($api->distribui($unidade, $usuario, $servico, $prioridade, $nomeCliente, $documentoCliente));
});

/**
 * Visualiza um atendimento que ainda não foi arquivado
 * GET /atendimento/1
 */
$app->get('/atendimento/:id', function($id) use ($app, $api, $server) {
    $server->checkAccess();
    echo json_encode($api->atendimento($id));
});

/**
 * Visualiza um atendimento que ainda não foi arquivado
 */
$app->get('/atendimento/:id/info', function($id) use ($app, $api, $server) {
    $server->checkAccess();
    echo json_encode($api->atendimentoInfo($id));
});

/*
 * Check extra route from configuration file
 */
$config = new Novosga\Config\ApiConfig();
foreach ($config->routes() as $pattern => $callable) {
    if (substr($pattern, 0, 1) !== '/') {
        $pattern = "/$pattern";
    }
    $app->any("/extra{$pattern}", $callable);
}

// response

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Max-Age: 1000");
header("Access-Control-Allow-Headers: origin, x-requested-with, content-type");

$app->contentType('application/json');
$app->run();
