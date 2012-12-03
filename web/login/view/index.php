<?php
use \core\SGA;

$context = SGA::getContext();
$session = $context->getSession();
if ($session->get(SGA::K_LOGIN_ERROR)) {
    $error = $this->builder->tag('span', array('class' => 'ui-state-error'), $session->get(SGA::K_LOGIN_ERROR));
    $session->del(SGA::K_LOGIN_ERROR);
} else {
    $error = "";
}

$content = '
    <div>
        <label>' . _('Usuário') . ':</label>
        <input id="login_usu" type="text" name="user" />
    </div>
    <div>
        <label>' . _('Senha') . ':</label>
        <input id="senha_usu" type="password" name="pass" />
    </div>
' . $error;
$button = $this->builder->button(array(
    'type' => 'submit',
    'label' => _('Entrar')
));
$dialog = $this->builder->dialog(array(
    'id' => 'dialog-login',
    'title' => _('Login'),
    'content' => $content,
    'buttons' => $button,
    'draggable' => false,
    'closeble' => false
))
        ;
?>
<form id="login-form" action="?login&<?php echo SGA::K_PAGE ?>=validate" method="post">
    <div id="logo"></div>
    <div id="login">
        <?php echo $dialog ?>
    </div>
    <script type="text/javascript">$(document).ready(function() { $('#login_usu').focus() })</script>
</form>