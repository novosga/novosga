<?php
use \core\SGA;
?>
<div id="perfil">
    <div class="module-content">
        <div class="header">
            <h2><?php echo _('Perfil') ?></h2>
            <p><?php echo _('Visualize e atualize o seu perfil') ?></p>
        </div>
        <form method="post" action="<?php echo SGA::url() ?>">
            <?php 
                if ($salvo) {
                    echo $builder->success(_('Perfil atualizado com sucesso'));
                }
            ?>
            <div class="field">
                <label for="login"><?php echo _('Login') ?></label>
                <input id="login" type="text" class="w150" value="<?php SGA::out($usuario->getLogin())  ?>" disabled="true" />
            </div>
            <div class="field">
                <label for="nome"><?php echo _('Nome') ?></label>
                <input id="nome" type="text" name="nome" class="w150" value="<?php SGA::out($usuario->getNome())  ?>" />
            </div>
            <div class="field">
                <label for="sobrenome"><?php echo _('Sobrenome') ?></label>
                <input id="sobrenome" type="text" name="sobrenome" class="w300" value="<?php SGA::out($usuario->getSobrenome())  ?>" />
            </div>
            <div class="field">
                <?php
                    echo $builder->button(array(
                        'id' => 'btn-altera-senha',
                        'label' => _('Alterar senha'),
                        'icon' => 'ui-icon-alert',
                        'onclick' => "return SGA.Perfil.dialogSenha('" . _('Alterar') . "')"
                    ));
                ?>
            </div>
            <div class="buttons">
                <?php
                    echo $builder->button(array(
                        'type' => 'submit',
                        'class' => 'ui-button-primary',
                        'label' => _('Salvar')
                    ));
                ?>
            </div>
        </form>
    </div>
</div>
<div id="dialog-senha" title="<?php SGA::out(_('Alterar senha')) ?>" style="display:none">
    <div class="field required">
        <label for="senha_atual" class="w150"><?php SGA::out(_('Senha atual')) ?></label>
        <input id="senha_atual" type="password" />
    </div>
    <div class="field required">
        <label for="senha_nova" class="w150"><?php SGA::out(_('Nova senha')) ?></label>
        <input id="senha_nova" type="password" />
    </div>
    <div class="field required">
        <label for="senha_confirmacao" class="w150"><?php SGA::out(_('Confirmar nova senha')) ?></label>
        <input id="senha_confirmacao" type="password" />
    </div>
</div>
<script type="text/javascript">
    SGA.Perfil.labelSenhaAlterada = "<?php SGA::out(_('Senha alterada com sucesso')) ?>";
</script>