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
        <label class="w125"><?php echo _('Código') ?></label>
        <input type="text" name="codigo" value="<?php echo $model->getCodigo() ?>" />
    </div>
    <div class="field required">
        <label class="w125"><?php echo _('Nome') ?></label>
        <input type="text" name="nome" class="w200" value="<?php echo $model->getNome() ?>" />
    </div>
    <div class="field required">
        <label class="w125"><?php echo _('Grupo') ?></label>
        <?php
            echo $builder->select(array(
                'name' => 'id_grupo',
                'label' => _('Selecione'),
                'items' => $grupos,
                'default' => $model->getGrupo()
            ));
        ?>
    </div>
    <div class="field required">
        <label class="w125">Status</label>
        <?php
            echo $builder->select(array(
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