<?php

// wrapper para manter compatibilidade com o painel antigo

$_GET = array(
    'painel' => '',
    'version' => 1,
    'page' => 'servicos',
    'unidade' => isset($_GET['id_uni']) ? (int) $_GET['id_uni'] : 0
);

require(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'index.php');
