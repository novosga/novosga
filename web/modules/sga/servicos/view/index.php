<?php

echo $this->searchBar();

$status = function($model) {
    return $this->statusLabel($model->getStatus());
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

echo $this->table(
    array(_('Nome'),_('Status')),
    array($nome, $status),
    $items
);

?>
<script type="text/javascript">
    SGA.Servicos.orderTable('table-list');
</script>