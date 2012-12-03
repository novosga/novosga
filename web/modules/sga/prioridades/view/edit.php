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
<form id="crud-form" method="post" action="<?php echo SGA::url() ?>">
    <input type="hidden" name="id" value="<?php echo $model->getId() ?>" />
    <p class="required-desc"><?php echo _('Campos obrigatórios') ?></p>
    <div class="field required">
        <label for="nome" class="w125"><?php echo _('Nome') ?></label>
        <input id="nome" type="text" name="nome" class="w400" value="<?php echo $model->getNome() ?>" />
    </div>
    <div class="field required">
        <label for="descricao" class="w125"><?php echo _('Descrição') ?></label>
        <textarea id="descricao" name="descricao" class="w400 h100"><?php echo $model->getDescricao() ?></textarea>
    </div>
    <div class="field">
        <label for="peso" class="w125"><?php echo _('Peso') ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'peso',
                'name' => 'peso',
                'label' => _('Normal'),
                'items' => array(
                    1 => '1', 
                    2 => '2', 
                    3 => '3', 
                    4 => '4', 
                    5 => '5'
                ),
                'default' => $model->getPeso(),
                'class' => 'w100'
            ));
        ?>
    </div>
    <div class="field required">
        <label for="status" class="w125"><?php echo _('Status') ?></label>
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