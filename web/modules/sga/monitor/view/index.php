<?php
use \core\SGA;
use \core\util\Strings;
?>
<div id="monitor">
    <?php foreach ($servicos as $su): ?>
    <?php $id  = $su->getServico()->getId(); ?>
    <?php $empty  = $su->getFila()->size() == 0; ?>
    <div id="servico-<?php echo $id ?>" class="servico ui-corner-all ui-state-default <?php echo ($empty) ? 'empty' : '' ?>" data-id="<?php echo $id ?>">
        <span class="title"><?php SGA::out($su->getServico()->getNome()) ?></span>
        <ul class="fila">
            <?php 
                if (!$empty):
                    for ($i = 0; $i < $su->getFila()->size(); $i++): 
                        $senha = $su->getFila()->get($i)->getSenha(); 
                        $onclick = "SGA.Monitor.viewSenha({$senha->getNumero()})";
                    ?>
                    <li class="<?php SGA::out(($senha->isPrioridade() ? 'prioridade' : '')) ?>">
                        <a href="javascript:void(0)" onclick="<?php echo $onclick ?>" title="<?php SGA::out($senha->isPrioridade() ? $senha->getPrioridade()->getNome() : _('Atendimento Normal')) ?>">
                            <?php SGA::out($senha) ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                <?php else: ?>
                    <li class="empty"><?php SGA::out(_('Fila vazia')) ?></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</div>
<div id="dialog-monitor" title="<?php SGA::out(_('Atendimento')) ?>" style="display:none">
    <fieldset>
        <legend><?php SGA::out(_('Senha')) ?></legend>
        <div>
            <label><?php SGA::out(_('Número')) ?></label>
            <span id="senha_numero"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Prioridade')) ?></label>
            <span id="senha_prioridade"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Serviço')) ?></label>
            <span id="senha_servico"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Data chegada')) ?></label>
            <span id="senha_chegada"></span>
        </div>
    </fieldset>
    <fieldset>
        <legend><?php SGA::out(_('Cliente')) ?></legend>
        <div>
            <label><?php SGA::out(_('Nome')) ?></label>
            <span id="cliente_nome"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Documento')) ?></label>
            <span id="cliente_documento"></span>
        </div>
    </fieldset>
</div>
<div id="sga-clock" title="<?php echo Strings::doubleQuoteSlash(_('Data e hora no servidor')) ?>"></div>
<script type="text/javascript">
    $('.servico').each(function(i,v) {
        var servico = $(v);
        SGA.Monitor.ids.push(servico.data('id'));
    });
    SGA.Clock.init("sga-clock", <?php echo (time() * 1000) ?>);
    SGA.Monitor.atendimentoNormal = '<?php SGA::out(_('Atendimento Normal')) ?>';
    SGA.Monitor.init();
</script>