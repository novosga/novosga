<?php
use \core\SGA;
use \core\util\DateUtil;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $atendimento->getSenha() ?></title>
    <link type="text/css" rel="stylesheet" href="<?php echo 'css/style.css?v=' . SGA::VERSION ?>" />
    <link type="text/css" rel="stylesheet" href="<?php echo SGA::getContext()->getModulo()->getPath() . '/css/print.css?v=' . SGA::VERSION ?>" />
    <link rel="shortcut icon" href="images/favicon.png" />
</head>
<body onload="window.print(); window.close();">
    <div id="senha">
        <div id="senha-header">
            <?php echo SGA::getContext()->getUnidade()->getNome() ?> <br />
            <span class="data"><?php echo DateUtil::now("d/m/Y H:i") ?></span>
        </div>
        <div id="senha-body">
            <?php echo $atendimento->getSenha()->toString() ?>
            <span class="descricao">
                <?php echo $atendimento->getSenha()->getPrioridade()->getNome() ?>
            </span>
        </div>
        <div id="senha-footer">
            <?php echo SGA::getContext()->getUnidade()->getMensagemImpressao() ?>
        </div>
    </div>
</body>
</html>