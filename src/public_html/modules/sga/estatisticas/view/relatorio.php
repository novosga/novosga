<?php
use \novosga\SGA;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $relatorio->getTitulo() ?></title>
    <link type="text/css" rel="stylesheet" href="<?php echo 'css/style.css?v=' . SGA::VERSION ?>" />
    <link type="text/css" rel="stylesheet" href="<?php echo SGA::getContext()->getModulo()->getPath() . '/css/relatorio.css?v=' . SGA::VERSION ?>" />
    <link rel="shortcut icon" href="images/favicon.png" />
</head>
<body>
    <div id="report">
        <div id="report-header">
            <a href="javascript:window.print()" class="print">{% trans %}Imprimir{% endtrans %}</a>
            <h1><?php echo $relatorio->getTitulo() ?></h1>
        </div>
        <div id="report-body">
            <?php require(dirname(dirname(__FILE__)) . DS . 'relatorios' . DS . $relatorio->getArquivo() . '.php'); ?>
        </div>
        <div id="report-footer">
            <p><?php echo $relatorio->getTitulo() ?> - Novo SGA v<?php echo SGA::VERSION ?></p>
        </div>
    </div>
</body>
</html>