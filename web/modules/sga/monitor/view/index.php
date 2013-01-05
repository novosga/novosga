<?php
use \core\SGA;
use \core\util\Strings;
?>
<div id="monitor">
    <div class="menu">
        <h4><?php SGA::out(_('Senhas')) ?></h4>
        <ul>
            <li><?php 
                echo $builder->button(array(
                    'id' => 'btn-consultar',
                    'label' => _('Consultar'),
                    'onclick' => 'SGA.Monitor.Senha.consultar()'
                ));
            ?></li>
            <li><?php 
                echo $builder->button(array(
                    'id' => 'btn-reativar',
                    'label' => _('Reativar'),
                    'onclick' => 'SGA.Monitor.Senha.reativar()'
                ));
            ?></li>
            <li><?php 
                echo $builder->button(array(
                    'id' => 'btn-cancelar',
                    'class' => 'ui-button-error',
                    'label' => _('Cancelar'),
                    'onclick' => 'SGA.Monitor.Senha.cancelar()'
                ));
            ?></li>
        </ul>
    </div>
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
                        $onclick = "SGA.Monitor.Senha.view({$senha->getNumero()})";
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
<div id="dialog-busca" title="<?php SGA::out(_('Busca')) ?>" style="display:none">
    <div>
        <label for="numero_busca"><?php SGA::out(_('Número')) ?></label>
        <input id="numero_busca" type="text" maxlength="5" />
    </div>
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