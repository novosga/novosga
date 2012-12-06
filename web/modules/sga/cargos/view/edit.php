<?php
use \core\SGA;

if ($message) {
    if ($message['success']) {
        echo $builder->success($message['message']);
    } else {
        echo $builder->error($message['message']);
    }
}

?>
<form id="crud-form" method="post" action="<?php SGA::out(SGA::url()) ?>">
    <input type="hidden" name="id" value="<?php SGA::out($model->getId()) ?>" />
    <p class="required-desc"><?php SGA::out(_('Campos obrigatórios')) ?></p>
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
    <div class="buttons">
        <?PHP
            echo $builder->button(array(
                'type' => 'submit',
                'label' => _('Salvar'),
                'class' => 'ui-button-primary btn-save'
            ));
            echo $builder->button(array(
                'type' => 'link',
                'label' => _('Voltar'),
                'href' => $context->getModule()->link('index'),
                'class' => 'btn-back'
            ));
        ?>
    </div>
    <script type="text/javascript">
        SGA.Form.validate('crud-form');
    </script>
</form>