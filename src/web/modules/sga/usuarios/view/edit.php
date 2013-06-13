<?php
use \core\SGA;

$checkboxAll = function() {
    return '<input type="checkbox" onchange="SGA.Usuarios.tableCheckAll(this);" />';
}

?>
<form id="crud-form" method="post" action="<?php SGA::out(SGA::url()) ?>">
    <input type="hidden" name="id" value="<?php SGA::out($model->getId()) ?>" />
    <div id="tabs">
        <ul>
            <li><a href="#tab-geral"><?php SGA::out(_('Geral')) ?></a></li>
            <li><a href="#tab-acesso"><?php SGA::out(_('Acesso')) ?></a></li>
        </ul>
        <div id="tab-geral">
            <div class="field required">
                <label for="login" class="w125"><?php SGA::out(_('Nome de usuário')) ?></label>
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
            <?php if (strlen($model->getSenha())): // nao exibe opcao para alterar senha de usuarios do LDAP ?>
                <div class="field">
                    <?php
                        echo $builder->button(array(
                            'id' => 'btn-altera-senha',
                            'label' => _('Alterar senha do usuário'),
                            'icon' => 'ui-icon-alert',
                            'onclick' => "return SGA.Usuarios.dialogSenha('" . _('Alterar') . "')"
                        ));
                    ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!$id): // criando usuario, entao pede senha ?>
                <div class="field required">
                    <label for="senha" class="w125"><?php SGA::out(_('Senha')) ?></label>
                    <input id="senha" type="password" name="senha" />
                </div>
                <div class="field required">
                    <label for="confirmacao" class="w125"><?php SGA::out(_('Confirmar senha')) ?></label>
                    <input id="confirmacao" type="password" name="senha2" />
                </div>
            <?php endif; ?>
        </div>
        <div id="tab-acesso">
            <fieldset>
                <legend><?php SGA::out(_('Lotações')) ?></legend>
                <?php 
                    echo $builder->button(array(
                        'id' => 'btn-add-lotacao',
                        'class' => 'btn-multi-add',
                        'label' => _('Adicionar'),
                        'icon' => 'ui-icon-plus',
                        'onclick' => "return SGA.Usuarios.addLotacao()"
                    ));
                ?>
                <p class="info"><?php SGA::out(_('Lotação é o par de grupo e cargo, utilizado para definir o papel e acesso do usuário às unidades.')) ?></p>
                <?php
                    $items = array();
                    foreach ($lotacoes as $lotacao) {
                        $items[] = array(
                            'id_grupo' => $lotacao->getGrupo()->getId(),
                            'grupo' => $lotacao->getGrupo()->getNome(),
                            'id_cargo' => $lotacao->getCargo()->getId(),
                            'cargo' => $lotacao->getCargo()->getNome()
                        );
                    }
                    $checkbox = function($item) { 
                        $value = $item['id_grupo'] . "," . $item['id_cargo'];
                        $html = '<input type="checkbox" class="check-lotacao" onchange="SGA.Usuarios.multiCheck(this, \'btn-remove-lotacao\')" />';
                        $html .= '<input type="hidden" class="value" name="lotacoes[]" value="' . $value . '" />';
                        return $html;
                    };
                    $cargo = function($item) {
                        return '<a href="javascript:void(0)" onclick="SGA.Usuarios.permissoes(' . $item['id_cargo'] . ')" title="' . _('Visualizar permissões') . '">' . $item['cargo'] . '</a>';
                    };
                    echo $builder->table(array(
                        'id' => 'lotacoes',
                        'header' => array($checkboxAll, _('Grupo'), _('Cargo')),
                        'classes' => array('checkbox', 'grupo', 'cargo'),
                        'columns' => array($checkbox, 'grupo', $cargo),
                        'items' => $items
                    ));
                    echo $builder->button(array(
                        'id' => 'btn-remove-lotacao',
                        'label' => _('Remover selecionados'),
                        'disabled' => 'disabled',
                        'icon' => 'ui-icon-arrow-1-n',
                        'onclick' => "return SGA.Usuarios.delLotacoes(this)"
                    ));
                ?>
            </fieldset>
            <fieldset>
                <legend><?php SGA::out(_('Serviços')) ?></legend>
                <?php 
                    echo $builder->button(array(
                        'id' => 'btn-add-servico',
                        'class' => 'btn-multi-add',
                        'label' => _('Adicionar'),
                        'icon' => 'ui-icon-plus',
                        'onclick' => "return SGA.Usuarios.addServico()"
                    ));
                ?>
                <p class="info"><?php SGA::out(_('Serviços que o usuário (atendente) atende em cada lotação.')) ?></p>
                <?php
                    $items = array();
                    foreach ($servicos as $servico) {
                        $items[] = array(
                            'id_unidade' => $servico->getUnidade()->getId(),
                            'unidade' => $servico->getUnidade()->getNome(),
                            'id_servico' => $servico->getServico()->getId(),
                            'servico' => $servico->getServico()->getNome()
                        );
                    }
                    $checkbox = function($item) { 
                        $value = $item['id_unidade'] . "," . $item['id_servico'];
                        $html = '<input type="checkbox" class="check-servico" onchange="SGA.Usuarios.multiCheck(this, \'btn-remove-servico\')" />';
                        $html .= '<input type="hidden" class="value" name="servicos[]" value="' . $value . '" />';
                        return $html;
                    };
                    echo $builder->table(array(
                        'id' => 'servicos',
                        'header' => array($checkboxAll, _('Serviço'), _('Unidade')),
                        'classes' => array('checkbox', 'servico', 'unidade'),
                        'columns' => array($checkbox ,'servico', 'unidade'),
                        'items' => $items
                    ));
                    echo $builder->button(array(
                        'id' => 'btn-remove-servico',
                        'label' => _('Remover selecionados'),
                        'disabled' => 'disabled',
                        'icon' => 'ui-icon-arrow-1-n',
                        'onclick' => "return SGA.Usuarios.delServicos(this)"
                    ));
                ?>
            </fieldset>
        </div>
    </div>
    <?php
        echo $view->editButtonsBar();
    ?>
</form>
<div id="dialog-add-lotacao" title="<?php SGA::out(_('Lotação')) ?>" style="display:none">
    <div class="field">
        <label for="add-grupo"><?php SGA::out(_('Grupo')) ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'add-grupo',
                'class' => 'w200',
                'label' => _('Selecione')
            ));
        ?>
    </div>
    <div class="field">
        <label for="add-cargo"><?php SGA::out(_('Cargo')) ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'add-cargo',
                'class' => 'w200',
                'label' => _('Selecione'),
                'items' => $cargos
            ));
        ?>
    </div>
</div>
<div id="dialog-add-servico" title="<?php SGA::out(_('Serviço')) ?>" style="display:none">
    <div class="field">
        <label for="add-unidade"><?php SGA::out(_('Unidade')) ?></label>
        <?php
            echo $builder->select(array(
                'id' => 'add-unidade',
                'class' => 'w200',
                'label' => _('Selecione'),
                'items' => $unidades,
                'onchange' => 'SGA.Usuarios.servicos_unidade($(this).val())'
            ));
        ?>
    </div>
    <div class="field">
        <label><?php SGA::out(_('Serviços')) ?></label>
        <div id="add-servicos">
            <ul></ul>
        </div>
    </div>
</div>
<div id="dialog-permissoes" title="<?php SGA::out(_('Permissões')) ?>" style="display:none">
    <h3><?php SGA::out(_('Módulos')) ?></h3>
    <ul></ul>
</div>
<?php if ($id > 0): // dialog mudar senha apenas para usuarios ja cadastrados ?>
<div id="dialog-senha" title="<?php SGA::out(_('Alterar senha')) ?>" style="display:none">
    <input id="senha_id" type="hidden" value="<?php echo $id ?>" />
    <div class="field required">
        <label for="senha_senha" class="w125"><?php SGA::out(_('Senha')) ?></label>
        <input id="senha_senha" type="password" />
    </div>
    <div class="field required">
        <label for="senha_confirmacao" class="w125"><?php SGA::out(_('Confirmar senha')) ?></label>
        <input id="senha_confirmacao" type="password" />
    </div>
</div>
<?php endif; ?>
<script type="text/javascript">
    SGA.Form.validate('crud-form');
    $('#tabs').tabs();
    SGA.Usuarios.labelAdd = "<?php SGA::out(_('Adicionar')) ?>";
    SGA.Usuarios.labelSenhaAlterada = "<?php SGA::out(_('Senha alterada com sucesso')) ?>";
    SGA.Usuarios.labelVisualizarPermissoes = "<?php SGA::out(_('Visualizar permissões')) ?>";
    SGA.Usuarios.multiDeleteLabel = "<?php SGA::out(_('Realmente deseja excluir?')) ?>";
    SGA.Usuarios.grupos_disponiveis();
</script>