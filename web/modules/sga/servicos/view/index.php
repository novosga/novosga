<?php
use \core\SGA;
?>
<div class="search">
    <form method="post" action="<?php SGA::out(SGA::url()) ?>">
        <span>
            <input id="search-box" type="text" name="s" value="<?php SGA::out($context->getRequest()->getParameter('s')) ?>" placeholder="<?php SGA::out(_('buscar')) ?>" />
            <script type="text/javascript">SGA.Form.searchBox('search-box')</script>
        </span>
        <?php
            echo $builder->button(array(
                'type' => 'submit',
                'label' => _('Buscar'),
                'icon' => 'ui-icon-search'
            ));
            echo $builder->button(array(
                'type' => 'link',
                'label' => _('Novo'),
                'href' => $context->getModule()->link('edit'),
                'class' => 'ui-button-primary btn-add'
            ));
        ?>
    </form>
</div>
<?php

$btnEdit = $builder->button(array(
    'id' => 'btn-edit-{id}',
    'type' => 'link',
    'label' => _('Editar'),
    'href' => $context->getModule()->link('edit', array('id' => '{id}'))
));

$nome = function($model) {
    $class = ($model->getMestre()) ? 'sub-servico' : 'servico-mestre';
    return '<span class="' . $class . '">' . $model->getNome() . '</span>';
};

$status = function($model) {
    if ($model->getStatus() == 1) { 
        $class = 'active';
        $label = _('Ativo');
    } else {
        $class = 'inactive';
        $label = _('Inativo');
    }
    return '<span class="' . $class . '">' . $label . '</span>';
};

echo $builder->table(array(
    'header' => array('#', _('Nome'), _('Status'), ''),
    'columns' => array('id', $nome, $status, $btnEdit),
    'classes' => array('', '', '', 'btns'),
    'items' => $items
));
