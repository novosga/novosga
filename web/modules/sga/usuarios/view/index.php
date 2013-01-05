<?php

echo $this->searchBar();

$login = function($model) {
    return '<span class="strong">' . $model->getLogin() . '</span>';
};

$status = function($model) {
    return $this->statusLabel($model->getStatus());
};

echo $this->table(
    array(_('Login'), _('Nome'), _('Status')),
    array($login, 'nome', $status),
    $items
);

