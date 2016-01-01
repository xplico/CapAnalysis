<?php
/**
 * Essential : Minimal PHP Framework
 * Copyright 2012-2013, Gianluca Costa (http://www.xplico.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */

$_view_vars = array();
$esalert = null;

// abilitazione dei log
if (isset($enable_dbg)) {
    ini_set('display_errors', $enable_dbg);
    error_reporting(E_ALL);
}

// funzione caricamento modelli
function LoadModels($model_dir) {
    $files = scandir($model_dir);
    foreach ($files as $file) {
		if (strstr($file, '.php') != FALSE) {
	    	include $model_dir.'/'.$file;
		}
    }
}

// funzione per il caricamento dei contenuti
function LoadPageContent_bis($file) {
    ob_start(); // start buffer
    include($file);
    $tmp = addslashes(ob_get_contents()); // assign buffer contents to variable
    ob_end_clean(); // end buffer and remove buffer contents
    eval("\$page_content=\"$tmp\";");
    return $page_content;
}

function LoadPageContentb($file) {
    ob_start(); // start buffer
    include($file);
    $tmp = ob_get_contents(); // assign buffer contents to variable
    ob_end_clean(); // end buffer and remove buffer contents
    return $tmp;
}

function LoadPageContent($file) {
    global $_view_vars;
    
    // espandiamo le variabili
    foreach ($_view_vars as $key => $val) {
        $$key = $val;
    }
    
    ob_start(); // start buffer
    include($file);
    $tmp = ob_get_clean(); // end buffer and remove buffer contents
    return $tmp;
}

// impostazione di una variabile da passare alla parte di visualizzazione
function ViewVar($name, $value) {
    global $_view_vars;
    
    $_view_vars[$name] = $value;
}


// recupero del controller e della pagina di visualizzazione
function ControllerPage() {
    $controller = 'default';
    $page = 'default';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['url']) && $_GET['url'] != '') {
            $elements = explode("/", $_GET['url']);
            $controller = $elements[0];
            if (isset($elements[1])) {
                $page = $elements[1];
    	    }
        }
    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_SERVER['QUERY_STRING'] != '') {
            $qs = explode('=', $_SERVER['QUERY_STRING']);
            if ($qs[0] == 'url') {
                $first = explode('&', $qs[1]);
                $elements = explode('/', $first[0]);
                $controller = $elements[0];
                if (isset($elements[1])) {
                    $page = $elements[1];
        		}
            }
        }
    }
    
    return array($controller, $page);
}

// salvataggio di una variabile nella sessione
function SesVarSet($name, $value) {
    $_SESSION[$name] = $value;
}

// recupero di una variabile dalla sessione
function SesVarGet($name) {
    if (isset($_SESSION[$name]))
        return $_SESSION[$name];
    
    return false;
}

// verifica presenza di una variabile nella sessione
function SesVarCheck($name) {
    if (isset($_SESSION[$name]))
        return true;
    return false;
}

// eliminazione di una variabile dalla sessione
function SesVarUnset($name) {
    unset($_SESSION[$name]);
}

// cancellazione (integrale) della sessione
function SesDelete() {
    session_destroy();
}

// redirect ad un'altro controller-pagina
function EsRedir($controler = null, $page = null, $param = null) {
    global $ROOT_APP;
    if ($controler == null) {
        header('Location: '.$ROOT_APP);
    }
    else if ($page == null || $page == '') {
        header('Location: '.$ROOT_APP.$controler);
    }
    else {
        if ($param == null) {
            header('Location: '.$ROOT_APP.$controler.'/'.$page);
        }
        else {
            header('Location: '.$ROOT_APP.$controler.'/'.$page.'?'.$param);
        }
    }
    die();
}

function EsNewUrl($controler, $page = null, $param = null) {
    global $ROOT_APP;
    if ($page == null) {
        return $ROOT_APP.$controler;
    }
    else {
        if ($param == null)
            return $ROOT_APP.$controler.'/'.$page;
        else
            return $ROOT_APP.$controler.'/'.$page.'?'.$param;
    }
}

// indica la pagina richiesta attraverso il Browser
function EsPage() {
    global $page;
    return $page;
}

// modifica/reimposta la pagina di visualizzazione
function EsSetPage($new_page) {
    global $page;
    $page = $new_page;
}

// imposta il messeggio/allarme di notifica
function EsMessage($msg) {
    SesVarSet('esalert', $msg);
}

// imposta un template diverso da quello di default
function EsTemplate($tmp = null) {
    global $template;
	
    $template = $tmp;
}

// funzioni di ripulitura da codice malevolo
function EsSanitize($var) {
    $subt = array('<', '>', '"');
    if (is_array($var)) {
        $ret = array();
        foreach($var as $key => $elem) {
            $ret[$key] = str_replace($subt, '', $elem);
        }
    }
    else {
        $ret = str_replace($subt, '', $var);
    }
    return $ret;
}

// funzioni che forniscono le dir di lavoro ed uso del app
function RootDir() {
    global $ROOT_DIR;
    
    return $ROOT_DIR.'/app/';
}

function WorkingDir() {
    global $workdir;

    return $workdir;
}

function RootApp() {
    global $ROOT_APP;
    
    return $ROOT_APP;
}

function DebStop($data, $stop = true) {
    print_r($data);
    if ($stop)
        die();
}
