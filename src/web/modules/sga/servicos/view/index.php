<?php

echo $view->searchBar();

$status = function($model) use ($view) {
    return $view->statusLabel($model->getStatus());
};

$nome = function($model) {
    $class = 'servico-' . $model->getId();
    if ($model->getMestre()) {
        $mestre = $model->getMestre()->getId();
        $class .= ' sub-servico';
    } else {
        $mestre = 0;
        $class .= ' servico-mestre';
    }
    return '<span class="nome ' . $class . '" data-mestre="' . $mestre . '">' . $model->getNome() . '</span>';
};

echo $view->table(
    array(_('Nome'),_('Status')),
    array($nome, $status),
    $items
);

?>
<script type="text/javascript">
    SGA.Servicos.orderTable('table-list');
</script>