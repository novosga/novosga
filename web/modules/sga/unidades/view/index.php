<?php
use \core\SGA;
?>
<div class="search">
    <form method="post" action="<?php SGA::out(SGA::url()) ?>">
        <span>
            <input id="search-box" type="text" name="s" value="<?php SGA::out($context->getRequest()->getParameter('s')) ?>" placeholder="buscar" />
            <script type="text/javascript">SGA.Form.searchBox('search-box')</script>
        </span>
        <?php
            echo $builder->button(array(
                'type' => 'submit',
                'label' => 'Buscar',
                'icon' => 'ui-icon-search'
            ));
            echo $builder->button(array(
                'type' => 'link',
                'label' => 'Novo',
                'href' => $context->getModule()->link('edit'),
                'class' => 'ui-button-primary btn-add'
            ));
        ?>
    </form>
</div>
<?php

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

$btnEdit = $builder->button(array(
    'id' => 'btn-edit-{id}',
    'type' => 'link',
    'label' => 'Editar',
    'href' => $context->getModule()->link('edit', array('id' => '{id}'))
));

echo $builder->table(array(
    'header' => array('#', _('Código'), _('Nome'), _('Grupo'), _('Status'), ''),
    'columns' => array('id', 'codigo', 'nome', 'grupo', $status, $btnEdit),
    'classes' => array('', '', '', '', '', 'btns'),
    'items' => $items
));
