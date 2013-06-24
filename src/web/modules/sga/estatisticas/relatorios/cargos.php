<?php foreach ($relatorio->getDados() as $dado): ?>
<div class="header">
    <h2><?php echo $dado['cargo'] ?></h2>
</div>
<table class="ui-data-table">
    <thead>
        <tr>
            <th><?php echo _('Módulos') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($dado['permissoes'] as $permissao): $i++; ?>
        <tr class="<?php echo $i % 2 == 0 ? 'par' : 'impar' ?>">
            <td><?php echo $permissao->getModulo()->getNome() ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endforeach; ?>