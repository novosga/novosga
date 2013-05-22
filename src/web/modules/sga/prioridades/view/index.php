<?php

echo $view->searchBar();

$status = function($model) use ($view) {
    return $view->statusLabel($model->getStatus());
};

echo $view->table(
    array(_('Nome'), _('Peso'), _('Status')),
    array('nome', 'peso', $status),
    $items
);
