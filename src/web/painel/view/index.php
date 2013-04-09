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
    <script type="text/javascript" src="js/buzz.js?v=<?php echo \core\SGA::VERSION ?>"></script>
    <script type="text/javascript" src="js/script.js?v=<?php echo \core\SGA::VERSION ?>"></script>
</head>
<body>
    <table id="layout">
        <tr class="top">
            <td colspan="2" class="mensagem">
                <div id="atual-mensagem" class="fittext">
                    <span><?php echo _('Atendimento') ?></span>
                </div>
            </td>
        </tr>
        <tr class="center">
            <td class="left">
                <div id="atual-senha" class="fittext">
                    <span>0000</span>
                </div>
            </td>
            <td class="right">
                <div id="atual-guiche" class="guiche fittext">
                    <span><?php echo _('Guichê') ?></span>
                </div>
                <div id="atual-guiche-numero" class="numero-guiche fittext">
                    <span>0</span>
                </div>
            </td>
        </tr>
        <tr class="bottom">
            <td colspan="2">
                <div id="historico">
                    <div class="titulo fittext">
                        <span><?php echo _('Últimas senhas chamadas') ?>:</span>
                    </div>
                    <div class="senhas">
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <!-- som executado ao chamar senha -->
    <audio id="audio-new" src="../media/audio/ekiga-vm.wav" style="display:none"></audio>
    <!-- menu retratil -->
    <div id="menu">
        <ul>
            <li><a href="javascript:void(0)" onclick="SGA.PainelWeb.Config.open()"><?php echo _('Configuração') ?></a></li>
            <li><a href="javascript:void(0)" onclick="SGA.PainelWeb.Layout.fullscreen()"><?php echo _('Fullscreen') ?></a></li>
        </ul>
    </div>
    <!-- janela de configuracao -->
    <div id="config">
        <div class="field unidade">
            <label><?php echo _('Unidade') ?></label>
            <select id="unidades" onchange="SGA.PainelWeb.Config.changeUnidade()">
                <option value=""><?php echo _('Selecione') ?></option>
                <?php foreach ($unidades as $unidade): ?>
                <option value="<?php echo $unidade->getId() ?>"><?php echo $unidade->getNome() ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field servicos">
            <label><?php echo _('Serviços') ?></label>
            <div id="servicos">
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            SGA.PainelWeb.Config.title = '<?php echo _('Configuração') ?>';
            SGA.PainelWeb.Config.btnSave = '<?php echo _('Salvar') ?>';
            SGA.PainelWeb.Config.lang = '<?php echo SGA::defaultClientLanguage(); ?>';
            SGA.PainelWeb.init();
        });
    </script>
</body>
</html>