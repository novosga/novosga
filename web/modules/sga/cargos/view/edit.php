<?php
use \core\SGA;

function hasPermissao($permissoes, $modulo) {
    foreach ($permissoes as $permissao) {
        if ($permissao->getModulo()->getId() == $modulo->getId()) {
            return true;
        }
    }
    return false;
}

echo $view->editMessages();

?>
<form id="crud-form" method="post" action="<?php SGA::out(SGA::url()) ?>">
    <input type="hidden" name="id" value="<?php SGA::out($model->getId()) ?>" />
    <div id="tabs">
        <ul>
            <li><a href="#tab-geral"><?php SGA::out(_('Geral')) ?></a></li>
            <li><a href="#tab-permissao"><?php SGA::out(_('Permissões')) ?></a></li>
        </ul>
        <div id="tab-geral">
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
        </div>
        <div id="tab-permissao">
            <?php 
                $tipo = 0;
                $tipos = array(_('Unidade'), _('Global'));
            ?>
            <h3><?php SGA::out(_('Módulos')) ?></h3>
            <div class="permissoes">
                <div class="modulos">
                    <h4><?php echo $tipos[$tipo] ?></h4>
                    <ul>
                    <?php 
                        foreach ($modulos as $modulo) {
                            if ($modulo->getTipo() != $tipo) {
                                $tipo = $modulo->getTipo();
                                echo '</ul></div><div class="modulos"><h4>' . $tipos[$tipo] . '</h4><ul>';
                            }
                            ?>
                            <li>
                                <input id="modulo-<?php echo $modulo->getId() ?>" type="checkbox" name="permissoes[]" value="<?php echo $modulo->getId() ?>" <?php echo hasPermissao($permissoes, $modulo) ? 'checked="checked"' : '' ?> />
                                <label for="modulo-<?php echo $modulo->getId() ?>"><?php echo $modulo->getNome() ?></label>
                            </li>
                            <?php 
                        } 
                    ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
        echo $view->editButtonsBar();
    ?>
</form>
<script type="text/javascript">
    $('#tabs').tabs();
    SGA.Form.validate('crud-form');
</script>