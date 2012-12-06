<?php
use \core\SGA;


function atendimentoInfo($atendimento) {
    if ($atendimento) {
        ?>
        <h3><?php echo _('Atendimento') ?></h3>
        <ul class="senha_info <?php echo $atendimento->getSenha()->isPrioridade() ? ' prioridade' : '' ?>">
            <li class="numero">
                <span class="label"><?php echo _('Senha') ?></span>
                <span class="value"><?php echo $atendimento->getSenha()->toString() ?></span>
            </li>
            <li class="servico">
                <span class="label"><?php echo _('Serviço') ?></span>
                <span class="value"><?php echo $atendimento->getServicoUnidade()->getNome() ?></span>
            </li>
            <li class="prioridade">
                <span class="label"><?php echo _('Prioridade') ?></span>
                <span class="value"><?php echo $atendimento->getSenha()->getPrioridade()->getNome() ?></span>
            </li>
            <li class="nome">
                <span class="label"><?php echo _('Nome') ?></span>
                <span class="value"><?php echo $atendimento->getCliente()->getNome() ?></span>
            </li>
        </ul>
        <?php
    }
}

function btnControl($label, $action) {
    ?>
    <button class="btn-control <?php echo $action ?>" onclick="SGA.Atendimento.<?php echo $action ?>()"><?php echo _($label) ?></button>
    <?php
}

$guiche = $context->getUser()->getGuiche();

?>
<div id="dialog-guiche" title="<?php echo _('Guichê') ?>" style="display:none">
    <form id="guiche_form" action="<?php echo SGA::url('set_guiche') ?>" method="post">
        <div>
            <label><?php echo _('Número') ?></label>
            <input type="text" id="numero_guiche" name="guiche" maxlength="3" class="w50" value="<?php echo $context->getCookie()->get('guiche') ?>" />
        </div>
    </form>
    <script type="text/javascript">
        $('#guiche_form').on('submit', function() {
            var numero = parseInt($('#numero_guiche').val().trim());
            if (isNaN(numero) || numero <= 0) {
                $('#numero_guiche').val('');
                return false;
            }
            return true;
        });
    </script>
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
        <div id="controls">
            <div id="chamar" class="control" style="display:none">
                <?php btnControl('Chamar próximo', 'chamar') ?>
            </div>
            <div id="iniciar" class="control" style="display:none">
                <?php 
                    atendimentoInfo($atendimento);
                    btnControl('Chamar novamente', 'chamar');
                    btnControl('Iniciar atendimento', 'iniciar') ;
                ?>
            </div>
            <div id="encerrar" class="control" style="display:none">
                <?php 
                    atendimentoInfo($atendimento); 
                    btnControl('Encerrar atendimento', 'encerrar');
                ?>
            </div>
        </div>
        <div id="fila">
            <span><?php echo _('Minha fila') ?>:</span>
            <ul></ul>
        </div>
    </div>
    <script type="text/javascript">
        <?php
            $status = ($atendimento) ? $atendimento->getStatus() : 1;
        ?>
        SGA.Atendimento.init(<?php echo $status ?>);
    </script>
    <?php
}
?>
