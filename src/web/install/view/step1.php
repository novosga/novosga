<?php
use \core\SGA;
use \install\InstallData;
use \install\InstallView;
use \core\view\TemplateBuilder;

$fatal = false;
$builder = new TemplateBuilder();

$context = SGA::getContext();
$adapter = InstallData::$dbTypes[$context->getSession()->get('adapter_driver')];

/*
 * minimum requirements
 */
$requiredsSetup = array(
    array('name' => 'PHP', 'key' => 'php', 'version_required' => '5.3.0', 'ext' => false),
    array('name' => 'PDO', 'key' => 'pdo', 'version_required' => '1.0.0', 'ext' => true),
    array('name' => $adapter['label'], 'key' => $adapter['driver'], 'version_required' => $adapter['version'], 'ext' => true),
    array('name' => 'Multibyte String', 'key' => 'mbstring', 'ext' => true)
);
foreach ($requiredsSetup as &$req) {
    $success = true;
    if ($req['ext']) {
        $success = extension_loaded($req['key']);
        if ($success) { 
            // if loaded then check version
            if (isset($req['version_required'])) {
                $req['version'] = phpversion($req['key']);
                $success = version_compare($req['version'], $req['version_required'], '>=');
                $req['result'] = $req['version'];
            } else {
                $req['version_required'] = '*';
                $req['result'] = _('Instalado');
            }
        } else {
            $req['result'] = _('Não instalado');
        }
    } else if ($req['key'] == 'php') {
        $req['version'] = phpversion();
        $success = version_compare($req['version'], $req['version_required'], '>=');
        $req['result'] = $req['version'];
    }
    if ($success) {
        $req['class'] =  'success';
    } else {
        $fatal = true;
        $req['class'] =  'failed';
    }
}

$tableSetup = $builder->table(array(
    'header' => array(
        _('Nome'), _('Versão requerida'), _('Versão instalada')
    ),
    'classes' => array(
        '', '', 'result'
    ),
    'columns' => array(
        array('class' => 'class', 'label' => 'name'),
        array('class' => 'class', 'label' => 'version_required'),
        array('class' => 'class', 'label' => 'result')
    ),
    'items' => $requiredsSetup
));

/*
 * file permissions
 */
$configFile = CORE_PATH . DS . 'Config.php';
$requiredsPermission = array(
    array('label' => _('Configuração do SGA'), 'file' => $configFile, 'required' => _('Escrita')),
    array('label' => $builder->tag('abbr', array('title' => _('Diretório utilizado para upload de arquivos')), _('Diretório temporário')), 'file' => sys_get_temp_dir(), 'required' => _('Escrita')),
    array('label' => $builder->tag('abbr', array('title' => _('Diretório utilizado para guardar os arquivos de sessão')), 'Session Save Path'), 'file' => session_save_path(), 'required' => _('Escrita')),
);
foreach ($requiredsPermission as &$req) {
    if (is_writable($req['file'])) {
        $req['result'] = _('Escrita');
        $req['class'] = 'success';
    } else {
        $fatal = true;
        $req['result'] = _('Somente leitura');
        $req['class'] = 'failed';
    }
}
$tablePermission = $builder->table(array(
    'header' => array(_('Arquivo'), '', _('Permissão requerida'), _('Permissão atual')),
    'classes' => array('', '', '', 'result'),
    'columns' => array(
        array('class' => 'class', 'label' => 'label'),
        array('class' => 'class', 'label' => 'file'),
        array('class' => 'class', 'label' => 'required'),
        array('class' => 'class', 'label' => 'result')
    ),
    'items' => $requiredsPermission
));

/*
 * php info
 */
$info = '?' . SGA::K_INSTALL . '&' . SGA::K_PAGE . '=info';
$tableInfo = $builder->table(array(
    'header' => array(_('Informações'), ''),
    'columns' => array('label', 'value'),
    'items' => array(
        array('label' => _('Sistema Operacional'), 'value' => php_uname()),
        array('label' => _('Banco de dados escolhido'), 'value' => $adapter['rdms']),
        array('label' => 'Server API', 'value' => php_sapi_name()),
        array('label' => 'Timezone', 'value' => date_default_timezone_get()),
        array('label' => 'PHP Info', 'value' => $builder->link(array('href' => $info, 'label' => _('Visualizar'), 'target' => '_blank')))
    )
));

?>
<div id="step_1">
    <?php
        if ($fatal) {
            echo $builder->error(_('A instalação não pode continuar enquanto os requisitos abaixos não forem satisfeitos. Favor verificar os itens em destaque.'));
            $context->getSession()->set(InstallView::ERROR, true);
        }
    ?>
    <div class="requirement">
        <h2><?php SGA::out(_('Requerimentos Mínimos')) ?></h2>
        <?php echo $tableSetup ?>
    </div>
    <div class="requirement">
        <h2><?php SGA::out(_('Permissões Requeridas')) ?></h2>
        <?php echo $tablePermission ?>
    </div>
    <div class="requirement">
        <h2><?php SGA::out(_('Informações do Ambiente')) ?></h2>
        <?php echo $tableInfo ?>
    </div>
</div>