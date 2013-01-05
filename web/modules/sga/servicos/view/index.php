<?php

echo $this->searchBar();

$status = function($model) {
    return $this->statusLabel($model->getStatus());
};

$nome = function($model) {
    $class = ($model->getMestre()) ? 'sub-servico' : 'servico-mestre';
    return '<span class="' . $class . '">' . $model->getNome() . '</span>';
};

echo $this->table(
    array(_('Nome'),_('Status')),
    array($nome, $status),
    $items
);
