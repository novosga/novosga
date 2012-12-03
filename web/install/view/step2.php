<?php
use \core\SGA;

// desabilita o proximo, para liberar so quando marcar o "aceito"
SGA::getContext()->getSession()->set(InstallView::ERROR, true);


$default = 'en';
$lang = SGA::defaultClientLanguage();
$filePrefix = 'COPYING_';
if (!file_exists($filePrefix . $lang)) {
    $lang = $default;
}

$license = file_get_contents($filePrefix . $lang);

?>
<div id="step_2">
    <div>
        <textarea id="license_textarea" readonly="readonly"><?php echo $license;?></textarea>
    </div>
    <div class="checkbox">
        <input type="checkbox" id="check_license" name="check_license" value="license_ok" onclick="SGA.Install.changeAcceptLicense();" />
        <label for="check_license">Li e concordo com os termos da licença</label>
    </div>
</div>
