<?php

echo $view->searchBar();

$status = function($model) use ($view) {
    return $view->statusLabel($model->getStatus());
};

echo $view->table(
    array(_('Código'), _('Nome'), _('Grupo'), _('Status')),
    array('codigo', 'nome', 'grupo', $status),
    $items
);
