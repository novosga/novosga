<?php
use \core\SGA;

function printModulos($builder, $modulos) {
    if (!empty($modulos)) {
        echo '<ul>';
        foreach ($modulos as $modulo) {
            $link = SGA::url(array(SGA::K_MODULE => $modulo->getChave()));
            $icon = $builder->tag('img', array('src' => $modulo->getPath() . DS . 'icon.png'));
            $label = $icon . '<span>' . $modulo->getNome() . '</span>';
            echo '<li>' . $builder->link(array('href' => $link, 'label' => $label)) . '</li>';
        }
        echo '</ul>';
    }
}
?>
<div id="home">
    <div class="welcome">
        <h1>Bem-vindo</h1>
        <p>Donec quam quam, venenatis non pretium ac, condimentum in risus.</p>
    </div>
    <div id="modules">
        <?php if ($unidade): ?>
        <div class="list unidade">
            <h2><?php echo _('Unidade') ?></h2>
            <p>Donec quam quam, venenatis non pretium ac, condimentum in risus.</p>
            <?php printModulos($builder, $modulosUnidade) ?>
        </div>
        <?php endif; ?>
        <div class="list global">
            <h2><?php echo _('Global') ?></h2>
            <p>Donec quam quam, venenatis non pretium ac, condimentum in risus.</p>
            <?php printModulos($builder, $modulosGlobal) ?>
        </div>
    </div>
</div>