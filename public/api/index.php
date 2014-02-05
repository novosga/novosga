<?php
require_once  '../../bootstrap.php';

$app = new Slim\Slim(array(
    'debug' => false
));

$em = $db->createEntityManager();
$server = new \Novosga\Api\OAuth2Server($em);

$api = new \Novosga\Api\ApiV1($em);

/*
 * API
 */

$app->error(function (Exception $e) use ($app) {
    echo json_encode(array('error' => $e->getMessage(), 'code' => $e->getCode()));
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
 * Distribui uma nova senha para atendimento. Requer autenticação, um access_token válido e ativo.
 * 
 * Parâmetros:
 *   int unidade (id da unidade do atendimento)
 *   int servico (id do serviço do atendimento)
 *   int prioridade (id da prioridade do atendimento, 1 para sem prioridade)
 *   string nome_cliente (nome do cliente a ser atendido)
 *   string doc_cliente (documento do cliente a ser atendido)
 * POST /distribui
 */
$app->post('/distribui', function() use ($app, $api, $server, $em) {
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
    $ab = new Novosga\Business\AtendimentoBusiness($em);
    $data = $ab->distribuiSenha($unidade, $usuario, $servico, $prioridade, $nomeCliente, $documentoCliente);
    echo json_encode($data);
});

$app->contentType('application/json');
$app->run();
