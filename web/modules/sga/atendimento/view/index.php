<?php
use \core\SGA;

$guiche = $context->getSession()->get('guiche');
?>
<div id="dialog-guiche" title="<?php echo _('Guichê') ?>" style="display:none">
    <form id="guiche_form" action="<?php echo SGA::url('set_guiche') ?>" method="post">
        <div>
            <label><?php echo _('Número') ?></label>
            <input type="text" id="numero_guiche" name="guiche" maxlength="3" class="w50" value="<?php echo $guiche ?>" />
        </div>
    </form>
</div>
<?php

// se ainda nao definiu o guiche, exibe automaticamente a dialog
if ($guiche <= 0) {
    ?>
    <script type="text/javascript">SGA.Atendimento.updateGuiche("<?php echo _('Salvar') ?>"); $('#guiche').focus();</script>
    <?php
} 
// guiche definido, exibe tela de atendimento
else {
    ?>
    <div id="atendimento">
        <div id="guiche">
            <span class="label"><?php echo _('Guichê') ?></span>
            <span class="numero"><?php echo $guiche ?></span>
            <a href="javascript:void(0)" onclick="SGA.Atendimento.updateGuiche('<?php echo _('Salvar') ?>')"><?php echo _('Alterar') ?></a>
        </div>
    </div>
    <?php
}
?>
