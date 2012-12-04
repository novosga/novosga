<?php

function blockServico(\core\view\TemplateBuilder $builder, \core\model\ServicoUnidade $servicoUnidade) {
    $servico = $servicoUnidade->getServico();
    $btnNormal = $builder->button(array(
        'class' => 'ui-button-primary',
        'label' => _('Normal'),
        'title' => _('Distribuir senha normal'),
        'data-id' => $servico->getId(),
        'onclick' => 'SGA.Triagem.senhaNormal(this)'
    ));
    $btnPrioridade = $builder->button(array(
        'class' => 'ui-button-error',
        'label' => _('Prioridade'),
        'data-id' => $servico->getId(),
        'onclick' => "SGA.Triagem.prioridade(this, '". _('Gerar prioridade') ."', '')",
        'title' => _('Distribuir senha com prioridade'),
    ));
    $buttons = '<span class="buttons">' . $btnNormal . $btnPrioridade . '</span>';
    $link = '<a href="javascript:void(0)" onclick="SGA.Triagem.servicoInfo(' . $servico->getId() . ', \'' . $servico->getNome() . '\')">' . $servico->getNome() . '</a>';
    $name = '<span class="servico" title="' . $servicoUnidade->getSigla() . ' - ' . $servico->getNome() . '">' . $link . '</span>';
    $total = '<span class="fila">
                <abbr id="total-aguardando-' . $servico->getId() . '" title="' . _('Aguardando atendimento') . '">-</abbr> / 
                <abbr id="total-senhas-' . $servico->getId() . '" title="' . _('Total de senhas do serviço') . '">-</abbr>
            </span>
    ';
    $text = '<span class="text">' . $name . $total . '</span>';
    $content = '<div class="ui-corner-all ui-state-default">' . $text . $buttons . '</div>';
    return $builder->tag('div', array(
                'id' => 'triagem-servico-' . $servico->getId(), 
                'data-id' => $servico->getId(), 
                'class' => 'triagem-servico'
        ), $content);
}

?>
<div id="client-info">
    <h4><?php echo _('Cliente') ?></h4>
    <div>
        <label for="cli_nome"><?php echo _('Nome') ?>:</label>
        <input type="text" id="cli_nome" class="nome" maxlength="50" />
    </div>
    <div>
        <label for="cli_doc"><?php echo _('Documento') ?>:</label>
        <input type="text" id="cli_doc" class="doc" maxlength="20" />
    </div>
</div>
<div id="triagem-servicos">
    <?php foreach ($servicos as $servico): ?>
    <?php echo blockServico($builder, $servico); ?>
    <?php endforeach; ?>
</div>
<!-- dialog para exibir informacoes do servico -->
<div id="dialog-servico" title="<?php echo _('Serviço') ?>" style="display:none">
    <div>
        <label><?php echo _('Descrição') ?></h3>
        <p class="descricao"></p>
    </div>
    <div>
        <h3><?php echo _('Subserviços') ?></h3>
        <ul class="subservicos notempty"></ul>
        <ul class="subservicos empty"><li><?php echo _('Não há subserviços') ?></li></ul>
    </div>
</div>
<!-- dialog para escolher a prioridade da senha -->
<div id="dialog-prioridade" title="<?php echo _('Prioridade') ?>" style="display:none">
    <ul>
        <?php foreach ($prioridades as $prioridade): ?>
        <li>
            <input id="prioridade-<?php echo $prioridade->getId() ?>" type="radio" name="prioridade" value="<?php echo $prioridade->getId() ?>" />
            <label for="prioridade-<?php echo $prioridade->getId() ?>"><?php echo $prioridade->getNome() ?></label>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<script type="text/javascript">
    $('.triagem-servico').each(function(i,v) {
        var servico = $(v);
        SGA.Triagem.ids.push(servico.data('id'));
    });
    SGA.Triagem.ajaxUpdate();
    SGA.Triagem.imprimir = <?php echo $unidade->getStatusImpressao() ? 'true' : 'false' ?>;
    setInterval(SGA.Triagem.ajaxUpdate, SGA.Triagem.ajaxInterval);
</script>