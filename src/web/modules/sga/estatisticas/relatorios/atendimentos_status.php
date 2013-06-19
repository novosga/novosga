<?php
use \core\util\DateUtil;

$isNumeracaoServico = \core\business\AtendimentoBusiness::isNumeracaoServico();
?>
<?php foreach ($relatorio->getDados() as $dado): ?>
<div class="header">
    <h2><?php echo $dado['unidade'] ?></h2>
    <p><?php echo sprintf(_('Período de %s a %s'), $dataInicial, $dataFinal) ?></p>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th><?php echo _('Senha|Bilhete') ?></th>
            <th><?php echo _('Data') ?></th>
            <th title="<?php echo _('Hora de Chamada') ?>"><?php echo _('Chamada') ?></th>
            <th title="<?php echo _('Hora do Início do atendimento') ?>"><?php echo _('Início') ?></th>
            <th title="<?php echo _('Hora do Fim do atendimento') ?>"><?php echo _('Fim') ?></th>
            <th title="<?php echo _('Serviço escolhido na triagem') ?>"><?php echo _('Serviço Triado') ?></th>
            <th><?php echo _('Status') ?></th>
            <th><?php echo _('Atendente') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dado['atendimentos'] as $a): ?>
        <tr>
            <td class=""><?php echo $a->getSiglaSenha() . ($isNumeracaoServico ? $a->getNumeroSenhaServico() : $a->getNumeroSenha()) ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataChegada(), _('d/m/Y')) ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataChamada(), 'H:i:s', '-') ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataInicio(), 'H:i:s', '-') ?></td>
            <td class=""><?php echo DateUtil::format($a->getDataFim(), 'H:i:s') ?></td>
            <td class=""><?php echo $a->getServico()->getNome() ?></td>
            <td class=""><?php echo $a->getNomeStatus() ?></td>
            <td class=""><?php echo ($a->getUsuario()) ? $a->getUsuario()->getLogin() : '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>