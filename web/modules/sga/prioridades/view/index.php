<?php

echo $this->searchBar();

$status = function($model) {
    return $this->statusLabel($model->getStatus());
};

echo $this->table(
    array(_('Nome'), _('Peso'), _('Status')),
    array('nome', 'peso', $status),
    $items
);
