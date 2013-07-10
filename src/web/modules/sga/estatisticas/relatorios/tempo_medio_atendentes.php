<?php 
use \core\util\DateUtil;
?>
<div class="header">
    <p><?php echo sprintf(_('Período de %s a %s'), $dataInicial, $dataFinal) ?></p>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th><?php echo _('Usuário') ?></th>
            <th><?php echo _('Atendimentos') ?></th>
            <th title="<?php echo _('Tempo médio de espera') ?>"><?php echo _('TME') ?></th>
            <th title="<?php echo _('Tempo médio de deslocamento') ?>"><?php echo _('TMD') ?></th>
            <th title="<?php echo _('Tempo médio de atendimento') ?>"><?php echo _('TMA') ?></th>
            <th><?php echo _('Tempo total') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($relatorio->getDados() as $dado): $i++; ?>
        <tr class="<?php echo $i % 2 == 0 ? 'par' : 'impar' ?>">
            <td class="strong"><?php echo $dado['atendente'] ?></td>
            <td><?php echo $dado['total'] ?></td>
            <td><?php echo DateUtil::secToTime($dado['espera']); ?></td>
            <td><?php echo DateUtil::secToTime($dado['deslocamento']); ?></td>
            <td><?php echo DateUtil::secToTime($dado['atendimento']); ?></td>
            <td><?php echo DateUtil::secToTime($dado['tempoTotal']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div>
    <h4><?php echo _('Legenda') ?></h4>
    <ul>
        <li><strong><?php echo _('TME') ?></strong>: <?php echo _('Tempo médio de espera') ?></li>
        <li><strong><?php echo _('TMD') ?></strong>: <?php echo _('Tempo médio de deslocamento') ?></li>
        <li><strong><?php echo _('TMA') ?></strong>: <?php echo _('Tempo médio de atendimento') ?></li>
    </ul>
</div>