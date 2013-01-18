<?php

echo $view->searchBar();

echo $builder->table(array(
    'id' => 'table-list',
    'header' => array('#', _('Nome'), _('Chave'), _('Autor'), ''),
    'columns' => array('id', 'nome', 'chave', 'autor', $view->buttonEdit()),
    'classes' => array('', '', '', '', 'btns'),
    'items' => $items
));