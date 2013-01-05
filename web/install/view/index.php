<?php
use \core\SGA;

/**
 * Copyright (C) 2009 DATAPREV - Empresa de Tecnologia e Informações da Previdência Social - Brasil
 *
 * Este arquivo é parte do programa SGA Livre - Sistema de Gerenciamento do Atendimento - Versão Livre
 *
 * O SGA é um software livre; você pode redistribuí­-lo e/ou modificá-lo dentro dos termos da Licença Pública Geral GNU como 
 * publicada pela Fundação do Software Livre (FSF); na versão 2 da Licença, ou (na sua opnião) qualquer versão.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer
 * MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt", junto com este programa, se não, escreva para a 
 * Fundação do Software Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 */
if (Config::SGA_INSTALLED) {
    SGA::redirect('/' . SGA::K_HOME);
}

require_once('InstallView.php');

$page = Arrays::value($_GET, SGA::K_PAGE);
if (empty($page)) {
    $context = SGA::getContext();
    $current_step = Arrays::value($_GET, SGA::K_INSTALL, 0);
    $view = new InstallView();
    $html = $view->render($context);
    $context->getResponse()->updateHeaders();
    $context->getSession()->setGlobal(SGA::K_INSTALL, $current_step);
    echo $html;
} else {
    require(dirname(__FILE__) . DS . $page);
}
