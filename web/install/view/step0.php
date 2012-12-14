<?php
use \core\SGA;
use \install\InstallView;

$context = SGA::getContext();
$currAdapter = $context->getSession()->get('adapter');
// desabilita o proximo, para liberar so quando marcar uma opção
$context->getSession()->set(InstallView::ERROR, $currAdapter == null);

function scriptHeader($file) {
    $header = array();
    $lines = file($file);
    $prefix = '-- @';
    foreach ($lines as $line) {
        if (strcmp(substr($line, 0, strlen($prefix)), $prefix) !== 0) {
            break;
        }
        preg_match_all('/@(.*)=(.*)\n/', $line, $matches); 
        if (sizeof($matches) >= 3) {
            $header[$matches[1][0]] = $matches[2][0];
        }
    }
    return $header;
}

$scripts = array();
$files = glob(dirname(dirname(__FILE__)) . DS . 'sql' . DS . 'create' . DS . '*.sql');
foreach ($files as $file) {
    $header = scriptHeader($file);
    $header['id'] = current(explode('.', basename($file)));
    $scripts[] = $header;
}

?>
<div id="step_0">
    <!--<img src="themes/sga.default/imgs/sga_passaro.png" />-->
    <h2>Bem-vindo a instalação da versão web do <?php SGA::out(SGA::NAME) ?>.</h2>
    <h3>Versão <?php SGA::out(SGA::VERSION) ?></h3>
    <p>Favor escolher o seu sistema de banco de dados preferido a partir dos listados abaixo:</p>
    <ul class="adapters">
        <?php foreach ($scripts as $script): ?>
        <li id="adapter-<?php SGA::out($script['id']) ?>" class="ui-corner-all <?php SGA::out(($currAdapter == $script['id']) ? 'ui-state-highlight' : 'ui-state-default') ?>">
            <input id="<?php SGA::out($script['id']) ?>" type="radio" name="db" value="<?php SGA::out($script['id']) ?>" onclick="setTimeout(function() { SGA.Install.chooseAdapter('<?php SGA::out($script['id']) ?>')}, 100)" <?php SGA::out(($currAdapter == $script['id']) ? 'checked="checked"' : '') ?> />
            <label for="<?php SGA::out($script['id']) ?>">
                <span class="adapter"><?php SGA::out($script['adapter']) ?></span>
                <span class="info author"><?php SGA::out(_('Autor') . ': ' . $script['author']) ?></span>
                <span class="info date"><?php SGA::out(_('Data de criação') . ': ' . date('d/m/Y', strtotime($script['date']))) ?></span>
            </label>
        </li>
        <?php endforeach; ?>
    </ul>
    <p>Os requisitos para instalação do sistema irão depender da opção acima.</p>
    <script type="text/javascript"> SGA.Install.adapter = '<?php SGA::out($currAdapter) ?>';</script>
</div>