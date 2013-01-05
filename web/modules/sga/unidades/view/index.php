<?php

echo $this->searchBar();

$status = function($model) {
    return $this->statusLabel($model->getStatus());
};

echo $this->table(
    array(_('Código'), _('Nome'), _('Grupo'), _('Status')),
    array('codigo', 'nome', 'grupo', $status),
    $items
);
