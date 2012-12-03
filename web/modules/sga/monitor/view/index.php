<div id="monitor">
    <?php foreach ($servicos as $su): ?>
    <?php $id  = $su->getServico()->getId(); ?>
    <?php $empty  = $su->getFila()->size() == 0; ?>
    <div id="servico-<?php echo $id ?>" class="servico ui-corner-all ui-state-default <?php echo ($empty) ? 'empty' : '' ?>" data-id="<?php echo $id ?>">
        <span class="title"><?php echo $su->getServico()->getNome() ?></span>
        <ul class="fila">
            <?php 
                if (!$empty):
                    for ($i = 0; $i < $su->getFila()->size(); $i++): 
                        $senha = $su->getFila()->get($i)->getSenha(); 
                        $onclick = "SGA.Monitor.viewSenha({$senha->getNumero()})";
                    ?>
                    <li class="<?php echo ($senha->isPrioridade() ? 'prioridade' : '') ?>">
                        <a href="javascript:void(0)" onclick="<?php echo $onclick ?>" title="<?php echo ($senha->isPrioridade() ? $senha->getPrioridade()->getNome() : _('Atendimento Normal')) ?>">
                            <?php echo $senha ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                <?php else: ?>
                    <li class="empty"><?php echo _('Fila vazia') ?></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endforeach; ?>
</div>
<div id="dialog-monitor" title="<?php echo _('Atendimento') ?>" style="display:none">
    <fieldset>
        <legend><?php echo _('Senha') ?></legend>
        <div>
            <label><?php echo _('Número') ?></label>
            <span id="senha_numero"></span>
        </div>
        <div>
            <label><?php echo _('Prioridade') ?></label>
            <span id="senha_prioridade"></span>
        </div>
        <div>
            <label><?php echo _('Data chegada') ?></label>
            <span id="senha_chegada"></span>
        </div>
    </fieldset>
    <fieldset>
        <legend><?php echo _('Cliente') ?></legend>
        <div>
            <label><?php echo _('Nome') ?></label>
            <span id="cliente_nome"></span>
        </div>
        <div>
            <label><?php echo _('Documento') ?></label>
            <span id="cliente_documento"></span>
        </div>
    </fieldset>
</div>
<script type="text/javascript">
    $('.servico').each(function(i,v) {
        var servico = $(v);
        SGA.Monitor.ids.push(servico.data('id'));
    });
    SGA.Monitor.atendimentoNormal = '<?php echo _('Atendimento Normal') ?>';
    setInterval(SGA.Monitor.ajaxUpdate, SGA.Monitor.ajaxInterval);
</script>