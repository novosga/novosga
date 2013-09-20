<?php

$status = function($model) {
    if ($model->getStatus() == 1) { 
        $class = 'active';
        $label = _('Ativo');
    } else {
        $class = 'inactive';
        $label = _('Inativo');
    }
    return '<span class="' . $class . '">' . $label . '</span>';
};

?>
<table class="ui-data-table">
    <thead>
        <tr>
            <th>{% trans %}Serviço{% endtrans %}</th>
            <th>{% trans %}Situação{% endtrans %}</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($relatorio->getDados() as $servico): $i++; ?>
        <tr class="<?php echo $i % 2 == 0 ? 'par' : 'impar' ?>">
            <td class="strong"><?php echo $servico->getNome() ?></td>
            <td class="w100 center"><?php echo $status($servico) ?></td>
        </tr>
        <?php if (sizeof($servico->getSubServicos())): ?>
        <tr class="sub-table">
            <td colspan="2">
                <table class="subservicos">
                    <tbody>
                        <?php foreach ($servico->getSubServicos() as $subServico): ?>
                        <tr>
                            <td class="nome"><?php echo $subServico->getNome() ?></td>
                            <td class="w100 center"><?php echo $status($subServico) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>