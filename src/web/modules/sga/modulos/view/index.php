<?php

echo $view->searchBar();

echo $view->table(
    array(_('Nome'), _('Chave'), _('Autor')),
    array('nome', 'chave', 'autor'),
    $items
);