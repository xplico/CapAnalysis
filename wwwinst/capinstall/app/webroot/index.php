<?php
/**
 * Essential : Minimal PHP Framework
 * Copyright 2012-2013, Gianluca Costa (http://www.xplico.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */

// questo file NON VA MODIFICATO fa parte del core di questo piccolo framework

// indivisuazione root dell'applicazione
$sa = strlen('app/webroot/index.php');
$sb = strlen($_SERVER['DOCUMENT_ROOT']);
$ROOT_APP = substr($_SERVER['SCRIPT_FILENAME'], $sb, -$sa);
$WEB_ROOT_DIR = $_SERVER['DOCUMENT_ROOT'];
$ROOT_DIR  = substr($_SERVER['SCRIPT_FILENAME'], 0, -$sa);
if ($ROOT_APP == '')
    $ROOT_APP = '/';

// caricamento file delle configurazioni
if (file_exists('../configs/configs.php')) {
    include '../configs/configs.php';
}

// inizializzarione o recupero della sessione
session_save_path($sessiondir);
if (isset($es_session)) {
    session_name($es_session);
}
session_start();

// template custom non presente:
$template = null;

// disabilito la cache del browser
session_cache_limiter('nocache');
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

// caricamento delle funzioni di libreria
include '../../core/lib/essential_lib.php';

// per la generazione della pagina
ViewVar('ROOT_APP', $ROOT_APP);

// scelta della lingua
if (SesVarCheck('locale')) {
	$locale = SesVarGet('locale');
}
else {
    $locale = 'it_IT.utf-8';
}
setlocale(LC_ALL, $locale);
putenv('LC_ALL='.$locale);
putenv('LANG='.$locale); 
putenv('LANGUAGE='.$locale);

// Specify location of translation tables
bindtextdomain('default', dirname(__FILE__).'/../locale');
bind_textdomain_codeset('default', 'UTF-8');
// Choose domain
textdomain('default');

// caricamento di tutti i modelli
LoadModels('../models');

// individuazione controller
list($controller, $page) = ControllerPage();

// controller per l'elaborazione dati
if (!file_exists('../controllers/'.$controller.'.php')) {
    $controller = 'default';
    $page = 'index';
}

include '../controllers/'.$controller.'.php';

if (!isset($title_page)) {
    if ($controller != $page)
        $title_page = '..:: '.ucfirst($controller).'->'.ucfirst($page).' ::..';
    else
        $title_page = '..:: '.ucfirst($controller).' ::..';
}

// caricamento ed elaborazione del contenuto della pagina
if (file_exists('../pages/'.$controller.'/'.$page.'.php'))
    $page_content = LoadPageContent('../pages/'.$controller.'/'.$page.'.php');
else
    $page_content = '';

if (SesVarCheck('esalert')) {
	$esalert = SesVarGet('esalert');
}
SesVarUnset('esalert');

// caricamento e fusione con il template
if ($template == null)
    include '../template/default.php';
else
    include '../template/'.$template.'.php';

