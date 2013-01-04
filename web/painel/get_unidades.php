<?php

// wrapper para manter compatibilidade com o painel antigo

$_GET = array(
    'painel' => '',
    'version' => 1,
    'page' => 'unidades'
);

require(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'index.php');
