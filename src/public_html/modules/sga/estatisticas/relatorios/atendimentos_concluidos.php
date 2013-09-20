<?php
use \novosga\util\DateUtil;

$isNumeracaoServico = \novosga\business\AtendimentoBusiness::isNumeracaoServico();
?>
<?php foreach ($relatorio->getDados() as $dado): ?>
<div class="header">
    <h2><?php echo $dado['unidade'] ?></h2>
    <p><?php echo sprintf(_('Período de %s a %s'), $dataInicial, $dataFinal) ?></p>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th>{% trans %}Senha|Bilhete{% endtrans %}</th>
            <th>{% trans %}Cliente{% endtrans %}</th>
            <th>{% trans %}Data{% endtrans %}</th>
            <th title="{% trans %}Hora de Chamada') ?>"><?php echo _('Chamada{% endtrans %}</th>
            <th title="{% trans %}Hora do Início do atendimento') ?>"><?php echo _('Início{% endtrans %}</th>
            <th title="{% trans %}Hora do Fim do atendimento') ?>"><?php echo _('Fim{% endtrans %}</th>
            <th title="{% trans %}Tempo de duração do atendimento (Fim - Início)') ?>"><?php echo _('Duração{% endtrans %}</th>
            <th title="{% trans %}Tempo de permanência no local (Fim - Chegada)') ?>"><?php echo _('Permanência{% endtrans %}</th>
            <th title="{% trans %}Serviço escolhido na triagem') ?>"><?php echo _('Serviço Triado{% endtrans %}</th>
            <th>{% trans %}Atendente{% endtrans %}</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($dado['atendimentos'] as $a): $i++; ?>
        <tr class="<?php echo $i % 2 == 0 ? 'par' : 'impar' ?>">
            <td class=""><?php echo $a->getSiglaSenha() . ($isNumeracaoServico ? $a->getNumeroSenhaServico() : $a->getNumeroSenha()) ?></td>
            <td class=""><?php echo $a->getNomeCliente() ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataChegada(), _('d/m/Y')) ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataChamada(), 'H:i:s', '-') ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataInicio(), 'H:i:s', '-') ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataFim(), 'H:i:s') ?></td>
            <td class=""><?php echo ($a->getDataInicio()) ? DateUtil::secToTime(DateUtil::diff($a->getDataInicio(), $a->getDataFim())) : '-' ?></td>
            <td class=""><?php echo DateUtil::secToTime(DateUtil::diff($a->getDataChegada(), $a->getDataFim())) ?></td>
            <td class=""><?php echo $a->getServico()->getNome() ?></td>
            <td class=""><?php echo $a->getUsuario()->getLogin() ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>