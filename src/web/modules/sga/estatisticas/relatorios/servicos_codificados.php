<?php foreach ($relatorio->getDados() as $dado): ?>
<div class="header">
    <h2><?php echo $dado['unidade'] ?></h2>
    <p><?php echo sprintf(_('Período de %s a %s'), $dataInicial, $dataFinal) ?></p>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th><?php echo _('Serviço') ?></th>
            <th><?php echo _('Total') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dado['servicos'] as $s): ?>
        <tr>
            <td><?php echo $s['nome'] ?></td>
            <td><?php echo $s['total'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>