<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Painel Web | Novo SGA</title>
    <link rel="stylesheet" type="text/css" href="css/style.css?v=<?php echo \core\SGA::VERSION ?>" />
    <link rel="stylesheet" type="text/css" href="../themes/<?php echo \core\view\SGAView::THEME ?>/style.css" />
    <script type="text/javascript" src="../js/jquery.js?v=<?php echo \core\SGA::VERSION ?>"></script>
    <script type="text/javascript" src="../js/jquery-ui.js?v=<?php echo \core\SGA::VERSION ?>"></script>
    <script type="text/javascript" src="../js/script.js?v=<?php echo \core\SGA::VERSION ?>"></script>
    <script type="text/javascript" src="js/textfill.js?v=<?php echo \core\SGA::VERSION ?>"></script>
    <script type="text/javascript" src="js/buzz.js?v=<?php echo \core\SGA::VERSION ?>"></script>
    <script type="text/javascript" src="js/script.js?v=<?php echo \core\SGA::VERSION ?>"></script>
</head>
<body>
    <div class="top fittext">
        <span class="atual-mensagem"><?php echo _('Atendimento') ?></span>
    </div>
    <div class="body">
        <div id="atual-senha" class="senha fittext"><span>0000</span></div>
        <div class="guiche-block">
            <div id="atual-guiche" class="guiche fittext"><span><?php echo _('Guichê') ?></span></div>
            <div id="atual-guiche-numero" class="numero-guiche fittext"><span>0</span></div>
        </div>
    </div>
    <div class="bottom">
        <div class="historico-titulo fittext"><span><?php echo _('Últimas senhas chamadas') ?>:</span></div>
        <div id="historico-senhas" class="senhas"></div>
    </div>
    <!-- som executado ao chamar senha -->
    <audio id="audio-new" src="../media/audio/ekiga-vm.wav" style="display:none"></audio>
    <!-- menu retratil -->
    <ul id="menu" class="menu">
        <li><a href="javascript:void(0)" onclick="SGA.PainelWeb.Config.open()"><?php echo _('Configuração') ?></a></li>
        <li><a href="javascript:void(0)" onclick="SGA.PainelWeb.Layout.fullscreen()"><?php echo _('Fullscreen') ?></a></li>
    </ul>
    <!-- janela de configuracao -->
    <div id="config" class="config">
        <div class="unidade">
            <h3 class="block-title"><?php echo _('Unidade') ?></h3>
            <select id="unidades" onchange="SGA.PainelWeb.Config.changeUnidade()">
                <option value=""><?php echo _('Selecione') ?></option>
                <?php foreach ($unidades as $unidade): ?>
                <option value="<?php echo $unidade->getId() ?>"><?php echo $unidade->getNome() ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="servicos">
            <h3 class="block-title"><?php echo _('Serviços') ?></h3>
            <ul id="servicos-container" class="servicos-list">
            </ul>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            SGA.PainelWeb.Config.title = '<?php echo _('Configuração') ?>';
            SGA.PainelWeb.Config.btnSave = '<?php echo _('Salvar') ?>';
            SGA.PainelWeb.init();
        });
    </script>
</body>
</html>