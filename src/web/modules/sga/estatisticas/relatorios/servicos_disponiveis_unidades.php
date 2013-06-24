<?php foreach ($relatorio->getDados() as $dado): ?>
<div class="header">
    <h2><?php echo $dado['unidade'] ?></h2>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th><?php echo _('Sigla') ?></th>
            <th><?php echo _('Situação') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($dado['servicos'] as $su): $servico = $su->getServico(); $i++; ?>
        <tr class="<?php echo $i % 2 == 0 ? 'par' : 'impar' ?>">
            <td class="w25 center"><?php echo $su->getSigla() ?></td>
            <td class="strong"><?php echo $servico->getNome() ?></td>
        </tr>
        <?php if (sizeof($servico->getSubServicos())): ?>
        <tr class="sub-table">
            <td colspan="2">
                <table class="subservicos">
                    <tbody>
                        <?php foreach ($servico->getSubServicos() as $subServico): ?>
                        <tr>
                            <td class="nome"><?php echo $subServico->getNome() ?></td>
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
<?php endforeach; ?>