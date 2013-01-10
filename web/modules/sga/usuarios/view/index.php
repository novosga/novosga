<?php

echo $view->searchBar();

$login = function($model) {
    return '<span class="strong">' . $model->getLogin() . '</span>';
};

$status = function($model) use ($view) {
    return $view->statusLabel($model->getStatus());
};

echo $view->table(
    array(_('Login'), _('Nome'), _('Status')),
    array($login, 'nome', $status),
    $items
);

