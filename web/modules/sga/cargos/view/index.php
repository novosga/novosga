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

echo $builder->treeView(array(
    'title' => _('Nome'),
    'items' => $items,
    'buttons' => $btnEdit
));