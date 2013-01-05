<?php
use \core\SGA;

echo $this->editMessages();

?>
<form id="crud-form" method="post" action="<?php SGA::out(SGA::url()) ?>">
    <input type="hidden" name="id" value="<?php SGA::out($model->getId()) ?>" />
    <div class="field required">
        <label for="nome" class="w125"><?php SGA::out(_('Nome')) ?></label>
        <input id="nome" type="text" name="nome" class="w400" value="<?php SGA::out($model->getNome()) ?>" />
    </div>
    <div class="field required">
        <label for="descricao" class="w125"><?php SGA::out(_('Descrição')) ?></label>
        <textarea id="descricao" name="descricao" class="w400 h100"><?php SGA::out($model->getDescricao()) ?></textarea>
    </div>
    <?php if ($model->getId() == 0 || $model->getLeft() != 1): ?>
    <div class="field required">
        <label for="pai" class="w125"><?php SGA::out(_('Pai')) ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'pai',
                'name' => 'id_pai',
                'label' => _('Selecione'),
                'items' => $pais,
                'default' => ($model->getParent() ? $model->getParent()->getId() : 0),
                'class' => 'w400'
            ));
        ?>
    </div>
    <?php endif; ?>
    <?php
        echo $this->editButtonsBar();
    ?>
</form>
<script type="text/javascript">
    SGA.Form.validate('crud-form');
</script>