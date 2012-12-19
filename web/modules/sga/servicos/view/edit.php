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
    <div class="field">
        <label for="macro" class="w125"><?php SGA::out(_('Macro')) ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'macro',
                'name' => 'id_macro',
                'label' => _('Selecione'),
                'items' => $macros,
                'default' => ($model->getMestre() ? $model->getMestre()->getId() : 0),
                'class' => 'w400'
            ));
        ?>
    </div>
    <div class="field required">
        <label for="status" class="w125"><?php SGA::out(_('Status')) ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'status',
                'name' => 'status',
                'label' => _('Selecione'),
                'items' => array(
                    1 => _('Ativo'), 
                    0 => _('Inativo')
                ),
                'default' => $model->getStatus()
            ));
        ?>
    </div>
    <?php
        echo $this->editButtonsBar();
    ?>
</form>
<script type="text/javascript">
    SGA.Form.validate('crud-form');
</script>