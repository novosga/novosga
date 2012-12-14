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
        <label for="login" class="w125"><?php SGA::out(_('Login')) ?></label>
        <input id="login" type="text" name="login" class="w150" value="<?php SGA::out($model->getLogin()) ?>" onkeyup="SGA.Form.loginValue(this)" />
    </div>
    <div class="field required">
        <label for="nome" class="w125"><?php SGA::out(_('Nome')) ?></label>
        <input id="nome" type="text" name="nome" class="w200" value="<?php SGA::out($model->getNome()) ?>" />
    </div>
    <div class="field required">
        <label for="descricao" class="w125"><?php SGA::out(_('Sobrenome')) ?></label>
        <input id="descricao" type="text" name="sobrenome" class="w200" value="<?php SGA::out($model->getSobrenome()) ?>" />
    </div>
    <?php if ($id > 0): // ja cadastra o usuario como ativo ?>
    <div class="field required">
        <label for="status" class="w125">Status</label>
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
    <?php endif; ?>
    <?php if (!$id): // criando usuario, entao pede senha ?>
        <div class="field required">
            <label class="w125"><?php SGA::out(_('Senha')) ?></label>
            <input type="password" name="senha" />
        </div>
        <div class="field required">
            <label class="w125"><?php SGA::out(_('Confirmar senha')) ?></label>
            <input type="password" name="senha2" />
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