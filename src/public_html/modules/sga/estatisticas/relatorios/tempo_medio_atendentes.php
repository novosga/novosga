<?php 
use \novosga\util\DateUtil;
?>
<div class="header">
    <p><?php echo sprintf(_('Período de %s a %s'), $dataInicial, $dataFinal) ?></p>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th>{% trans %}Usuário{% endtrans %}</th>
            <th>{% trans %}Atendimentos{% endtrans %}</th>
            <th title="{% trans %}Tempo médio de espera') ?>"><?php echo _('TME{% endtrans %}</th>
            <th title="{% trans %}Tempo médio de deslocamento') ?>"><?php echo _('TMD{% endtrans %}</th>
            <th title="{% trans %}Tempo médio de atendimento') ?>"><?php echo _('TMA{% endtrans %}</th>
            <th>{% trans %}Tempo total{% endtrans %}</th>
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
    <h4>{% trans %}Legenda{% endtrans %}</h4>
    <ul>
        <li><strong>{% trans %}TME') ?></strong>: <?php echo _('Tempo médio de espera{% endtrans %}</li>
        <li><strong>{% trans %}TMD') ?></strong>: <?php echo _('Tempo médio de deslocamento{% endtrans %}</li>
        <li><strong>{% trans %}TMA') ?></strong>: <?php echo _('Tempo médio de atendimento{% endtrans %}</li>
    </ul>
</div>