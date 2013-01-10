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
        <h1><?php SGA::out(_('Bem-vindo')) ?></h1>
        <p><?php SGA::out(_('Novo SGA, Sistema de Gerenciamento de Atendimento baseado em web, de código aberto e gratuito.')) ?></p>
    </div>
    <div id="modules">
        <?php if ($unidade): ?>
        <div class="list unidade">
            <h2><?php SGA::out(_('Unidade')) ?></h2>
            <p><?php SGA::out(_('Visualize abaixo os módulos disponíveis para a sua unidade')) ?></p>
            <?php printModulos($builder, $modulosUnidade) ?>
        </div>
        <?php endif; ?>
        <?php if (sizeof($modulosGlobal)): ?>
        <div class="list global">
            <h2><?php SGA::out(_('Global')) ?></h2>
            <p><?php SGA::out(_('Visualize abaixo os módulos globais disponíveis que você possui acesso')) ?></p>
            <?php printModulos($builder, $modulosGlobal) ?>
        </div>
        <?php endif; ?>
    </div>
</div>