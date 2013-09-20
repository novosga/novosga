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
<?php foreach ($relatorio->getDados() as $dado): ?>
<div class="header">
    <h2><?php echo $dado['unidade'] ?></h2>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th>{% trans %}Usuário{% endtrans %}</th>
            <th>{% trans %}Nome{% endtrans %}</th>
            <th>{% trans %}Cargo{% endtrans %}</th>
            <th>{% trans %}Grupo{% endtrans %}</th>
            <th>{% trans %}Status{% endtrans %}</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($dado['lotacoes'] as $lotacao): $i++; ?>
        <tr class="<?php echo $i % 2 == 0 ? 'par' : 'impar' ?>">
            <td class="strong"><?php echo $lotacao->getUsuario()->getLogin() ?></td>
            <td><?php echo $lotacao->getUsuario()->getNomeCompleto() ?></td>
            <td><?php echo $lotacao->getCargo()->getNome() ?></td>
            <td><?php echo $lotacao->getGrupo()->getNome() ?></td>
            <td class="w75 center"><?php echo $status($lotacao->getUsuario()) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>