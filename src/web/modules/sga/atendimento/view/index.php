<?php
use \core\SGA;
use \core\util\Strings;

function atendimentoInfo($atendimento) {
    ?>
    <div class="senha">
        <h3 class="title"><?php SGA::out(_('Atendimento')) ?></h3>
        <ul class="info <?php SGA::out(($atendimento && $atendimento->getSenha()->isPrioridade()) ? ' prioridade' : '') ?>">
            <li class="numero">
                <span class="label"><?php SGA::out(_('Senha|Bilhete')) ?></span>
                <span class="value"><?php SGA::out(($atendimento) ? $atendimento->getSenha()->toString() : '') ?></span>
            </li>
            <li class="servico">
                <span class="label"><?php SGA::out(_('Serviço')) ?></span>
                <span class="value"><?php SGA::out(($atendimento) ? $atendimento->getServicoUnidade()->getNome() : '') ?></span>
            </li>
            <li class="nome-prioridade">
                <span class="label"><?php SGA::out(_('Prioridade')) ?></span>
                <span class="value"><?php SGA::out(($atendimento) ? $atendimento->getSenha()->getPrioridade()->getNome() : '') ?></span>
            </li>
            <li class="nome">
                <span class="label"><?php SGA::out(_('Nome')) ?></span>
                <span class="value"><?php SGA::out(($atendimento) ? $atendimento->getCliente()->getNome() : '') ?></span>
            </li>
        </ul>
    </div>
    <?php
}

function btnControl($label, $action) {
    ?>
    <button class="btn-control <?php SGA::out($action) ?>" onclick="SGA.Atendimento.<?php SGA::out($action) ?>(this)" title="<?php SGA::out($label) ?>"><?php SGA::out($label) ?></button>
    <?php
}

$guiche = $context->getUser()->getGuiche();

?>
<div id="dialog-guiche" title="<?php SGA::out(_('Guichê')) ?>" style="display:none">
    <form id="guiche_form" action="<?php SGA::out(SGA::url('set_guiche')) ?>" method="post">
        <div>
            <label class="w100"><?php SGA::out(_('Número')) ?></label>
            <input type="text" id="numero_guiche" name="guiche" maxlength="3" class="w50" value="<?php echo $context->getCookie()->get('guiche') ?>" />
        </div>
        <div>
            <label class="w100" title="<?php echo _('Tipo de Atendimento') ?>"><?php SGA::out(_('Atendimento')) ?></label>
            <select id="tipo_atendimento" name="tipo">
                <?php foreach ($tiposAtendimento as $v => $l): $c = $context->getCookie()->get('tipo'); ?>
                <option value="<?php echo $v ?>" <?php echo $c == $v ? 'selected="selected"' : '' ?>><?php echo $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <script type="text/javascript">
        $('#guiche_form').on('submit', function() {
            var numero = parseInt($('#numero_guiche').val());
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
    <script type="text/javascript">SGA.Atendimento.updateGuiche("<?php SGA::out(_('Salvar')) ?>"); $('#guiche').focus();</script>
    <?php
} 
// guiche definido, exibe tela de atendimento
else {
    ?>
    <div id="atendimento">
        <div id="guiche">
            <span class="label"><?php SGA::out(_('Guichê')) ?></span>
            <span class="numero"><?php SGA::out($guiche) ?></span>
            <a href="javascript:void(0)" onclick="SGA.Atendimento.updateGuiche('<?php SGA::out(_('Salvar') )?>')"><?php SGA::out(_('Alterar')) ?></a>
        </div>
        <div id="controls">
            <div id="chamar" class="control" style="display:none">
                <?php btnControl(_('Chamar próximo'), 'chamar') ?>
            </div>
            <div id="iniciar" class="control" style="display:none">
                <?php 
                    atendimentoInfo($atendimento);
                    btnControl(_('Chamar novamente'), 'chamar');
                    btnControl(_('Iniciar atendimento'), 'iniciar') ;
                    btnControl(_('Não compareceu'), 'nao_compareceu') ;
                ?>
            </div>
            <div id="encerrar" class="control" style="display:none">
                <?php 
                    atendimentoInfo($atendimento); 
                    btnControl(_('Encerrar atendimento'), 'encerrar');
                    btnControl(_('Erro de triagem'), 'erro_triagem');
                ?>
            </div>
            <div id="codificar" class="control" style="display:none">
                <div class="left">
                    <h3><?php SGA::out(_('Serviços disponíveis')) ?></h3>
                    <ul id="macro-servicos" class="items">
                        <?php 
                            foreach ($servicos as $usuarioServico): 
                                $servico = $usuarioServico->getServico(); 
                                $subservicos = $servico->getSubServicos();
                            ?>
                            <li id="servico-<?php echo $servico->getId() ?>">
                                <a href="javascript:void(0)" onclick="SGA.Atendimento.addServico(this)" data-id="<?php echo $servico->getId() ?>" title="<?php SGA::out(_('Adicionar')) ?>"><?php SGA::out($servico->getNome()) ?></a>
                                <?php if (sizeof($subservicos)): ?>
                                    <ul>
                                    <?php foreach ($subservicos as $subservico): ?>
                                        <li id="servico-<?php echo $subservico->getId() ?>">
                                            <a href="javascript:void(0)" onclick="SGA.Atendimento.addServico(this)" data-id="<?php echo $subservico->getId() ?>" title="<?php SGA::out(_('Adicionar')) ?>"><?php SGA::out($subservico->getNome()) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                            <?php 
                            endforeach; 
                        ?>
                    </ul>
                </div>
                <div class="left">
                    <h3><?php SGA::out(_('Serviços realizados')) ?></h3>
                    <ul id="servicos-realizados" class="items">
                    </ul>
                    <div class="redirecionar">
                        <input id="encerrar-redirecionar" type="checkbox" value="1" />
                        <label for="encerrar-redirecionar" title="<?php echo _('Marque para codificar e redirecionar o atendimento atual') ?>"><?php echo _('Redirecionar atendimento ao encerrar') ?></label>
                    </div>
                    <?php 
                        btnControl('Encerrar atendimento', 'codificar');
                        btnControl('Erro de triagem', 'erro_triagem');
                    ?>
                </div>
            </div>
        </div>
        <div id="fila">
            <span><?php SGA::out(_('Minha fila')) ?>:</span>
            <ul></ul>
        </div>
    </div>
    <div id="dialog-redirecionar" title="<?php SGA::out(_('Redirecionar')) ?>" style="display:none">
        <div class="field">
            <label for="redirecionar_servico"><?php SGA::out(_('Novo Serviço')) ?></label>
            <select id="redirecionar_servico" class="w300">
                <option value=""><?php SGA::out(_('Selecione')) ?></option>
                <?php foreach ($servicosIndisponiveis as $servico): ?>
                <option value="<?php echo $servico->getServico()->getId() ?>"><?php SGA::out($servico->getNome()) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div id="sga-clock" title="<?php echo Strings::doubleQuoteSlash(_('Data e hora no servidor')) ?>"></div>
    <!-- som executado quando a fila deixa de estar fazia -->
    <audio id="audio-new" src="media/audio/ekiga-vm.wav" style="display:none"></audio>
    <script type="text/javascript">
        <?php
            $status = ($atendimento) ? $atendimento->getStatus() : 1;
        ?>
        SGA.Clock.init("sga-clock", <?php echo (time() * 1000) ?>);
        SGA.Atendimento.filaVazia = '<?php SGA::out(_('Fila vazia')) ?>';
        SGA.Atendimento.remover = '<?php SGA::out(_('Remover')) ?>';
        SGA.Atendimento.labelRedirecionar = '<?php SGA::out(_('Redirecionar')) ?>';
        SGA.Atendimento.marcarErroTriagem = '<?php SGA::out(_('Realmente deseja marcar como erro de triagem?')) ?>';
        SGA.Atendimento.marcarNaoCompareceu = '<?php SGA::out(_('Realmente deseja marcar como não compareceu?')) ?>';
        SGA.Atendimento.nenhumServicoSelecionado = '<?php SGA::out(_('Nenhum serviço selecionado')) ?>';
        SGA.Atendimento.init(<?php SGA::out($status) ?>);
    </script>
    <?php
}