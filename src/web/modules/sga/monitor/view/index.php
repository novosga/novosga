<?php
use \core\SGA;
use \core\util\Strings;
use \core\util\DateUtil;
?>
<div id="monitor">
    <div class="search">
        <input type="text" id="buscar-senha" placeholder="<?php echo _('buscar senha') ?>" />
        <?php 
            echo $builder->button(array(
                'id' => 'btn-open-consulta',
                'label' => _('Consultar'),
                'class' => 'ui-button-primary',
                'onclick' => 'SGA.Monitor.Senha.consulta()'
            ));
        ?>
    </div>
    <?php foreach ($servicos as $su): ?>
    <?php $id  = $su->getServico()->getId(); ?>
    <?php $empty  = $su->getFila()->size() == 0; ?>
    <div id="servico-<?php echo $id ?>" class="servico ui-corner-all ui-state-default <?php echo ($empty) ? 'empty' : '' ?>" data-id="<?php echo $id ?>">
        <span class="title"><?php SGA::out($su->getSigla() . ' - ' . $su->getNome()) ?></span>
        <ul class="fila">
            <?php 
                if (!$empty):
                    for ($i = 0; $i < $su->getFila()->size(); $i++): 
                        $atendimento = $su->getFila()->get($i); 
                        $senha = $atendimento->getSenha();
                        $onclick = "SGA.Monitor.Senha.view({$atendimento->getId()})";
                        $espera = DateUtil::secToTime(DateUtil::diff($atendimento->getDataChegada(), DateUtil::nowSQL()));
                        $title = "{$senha->getPrioridade()->getNome()} ({$espera})";
                    ?>
                    <li class="<?php SGA::out(($senha->isPrioridade() ? 'prioridade' : '')) ?>">
                        <a href="javascript:void(0)" onclick="<?php echo $onclick ?>" title="<?php echo $title ?>">
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
        <?php 
            echo $builder->button(array(
                'id' => 'btn-consultar',
                'label' => _('Consultar'),
                'class' => 'ui-button-primary',
                'onclick' => 'SGA.Monitor.Senha.consultar()'
            ));
        ?>
    </div>
    <div class="result">
        <table id="result_table" class="ui-data-table">
            <thead>
                <tr>
                    <th><?php SGA::out(_('Número')) ?></th>
                    <th><?php SGA::out(_('Serviço')) ?></th>
                    <th><?php SGA::out(_('Data chegada')) ?></th>
                    <th><?php SGA::out(_('Data início')) ?></th>
                    <th><?php SGA::out(_('Data fim')) ?></th>
                    <th><?php SGA::out(_('Triagem')) ?></th>
                    <th><?php SGA::out(_('Atendente')) ?></th>
                    <th><?php SGA::out(_('Situação')) ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<div id="dialog-view" title="<?php SGA::out(_('Atendimento')) ?>" style="display:none">
    <input id="senha_id" type="hidden" />
    <fieldset>
        <legend><?php SGA::out(_('Senha|Bilhete')) ?></legend>
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
        <div>
            <label><?php SGA::out(_('Tempo de espera')) ?></label>
            <span id="senha_espera"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Data início')) ?></label>
            <span id="senha_inicio"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Data fim')) ?></label>
            <span id="senha_fim"></span>
        </div>
        <div>
            <label><?php SGA::out(_('Situação')) ?></label>
            <span id="senha_status"></span>
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
    <div class="btns">
        <?php 
            echo $builder->button(array(
                'id' => 'btn-reativar',
                'class' => 'ui-button-primary',
                'label' => _('Reativar senha'),
                'onclick' => "SGA.Monitor.Senha.reativar($('#senha_id').val())"
            ));
            echo $builder->button(array(
                'id' => 'btn-transferir',
                'label' => _('Transferir / Alterar senha'),
                'onclick' => "SGA.Monitor.Senha.transfere($('#senha_id').val(), $('#senha_numero').text())"
            ));
            echo $builder->button(array(
                'id' => 'btn-cancelar',
                'class' => 'ui-button-error',
                'label' => _('Cancelar senha'),
                'onclick' => "SGA.Monitor.Senha.cancelar($('#senha_id').val())"
            ));
        ?>
    </div>
</div>
<div id="dialog-transfere" title="<?php SGA::out(_('Tranferir Senha')) ?>" style="display:none">
    <input id="transfere_id" type="hidden" />
    <div>
        <label><?php SGA::out(_('Senha|Bilhete')) ?></label>
        <span id="transfere_numero"></span>
    </div>
    <div>
        <label for="transfere_servico"><?php SGA::out(_('Novo serviço')) ?></label>
        <select id="transfere_servico">
            <?php foreach ($servicos as $su): ?>
            <option value="<?php echo $su->getServico()->getId() ?>"><?php echo $su->getNome() ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label><?php SGA::out(_('Nova prioridade')) ?></label>
        <select id="transfere_prioridade">
            <?php foreach ($prioridades as $prioridade): ?>
            <option value="<?php echo $prioridade->getId() ?>"><?php echo $prioridade->getNome() ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div id="sga-clock" title="<?php echo Strings::doubleQuoteSlash(_('Data e hora no servidor')) ?>"></div>
<script type="text/javascript">
    $('.servico').each(function(i,v) {
        var servico = $(v);
        SGA.Monitor.ids.push(servico.data('id'));
    });
    SGA.Clock.init("sga-clock", <?php echo (time() * 1000) ?>);
    SGA.Monitor.labelTransferir = '<?php SGA::out(_('Transferir')) ?>';
    SGA.Monitor.alertCancelar = '<?php SGA::out(_('Deseja realmente cancelar essa senha?')) ?>';
    SGA.Monitor.alertReativar = '<?php SGA::out(_('Deseja realmente reativar essa senha?')) ?>';
    SGA.Monitor.init();
</script>