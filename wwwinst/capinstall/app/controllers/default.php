<?php
// titolo
$title_page = 'CapInstall';

// js aggiuntivo (se necessario)
//$custom_js = '**.js';

// css aggiuntivo (se necessario)
//$custom_css = '**.css';

// top menu (solo una voce a sinistra)
$header_left = array(
					 array('help' => 'CapAnalysis Manual', 'link' => $ROOT_APP.'manual/intro', 'title' => 'Manual'),
					 array('help' => 'Support', 'link' => 'mailto:support@capanalysis.net', 'title' => 'Support'),
				);
$header_left_active = -1;

$header_right = array(
					 array('help' => '', 'link' => $ROOT_APP.'default/credits', 'title' => 'Credits'),
				);
if ($page == 'credits')
	$header_right_active = 0;
else
	$header_right_active = -1;


// Is CapAnalysis started?
$running = 0;
if (file_exists('/var/run/capana.pid')) {
	$foca = fopen('/var/run/capana.pid', 'r');
	if ($foca) {
		$capana_pid = fgets($foca, 200);
		fclose($foca);
		$foca = popen('ps -p '.$capana_pid.' | grep capanalysis', 'r');
		if ($foca) {
			while (!feof($foca)) {
				$capana_pid = fgets($foca, 200);
				if (strstr($capana_pid, 'capanalysis'))
					$running = 1;
			}
			pclose($foca);
		}
	}
}
ViewVar('running', $running);

if ($running) {
    while (!file_exists($ROOT_DIR.'/tmp/db.stat'))
        sleep(1);
}

// database connection
if (file_exists($ROOT_DIR.'/tmp/db.stat')) {
	$db = file($ROOT_DIR.'/tmp/db.stat');
	if (count($db) != 0) {
		$db[0] = rtrim($db[0], "\n");
		switch ($db[0]) {
		case 'OK':
			ViewVar('db_con', True);
			ViewVar('db_usr', True);
			ViewVar('db_tables', True);
			break;

		case 'USR':
			ViewVar('db_con', True);
			ViewVar('db_usr', False);
			ViewVar('db_tables', False);
			break;

		case 'DB':
			ViewVar('db_con', True);
			ViewVar('db_usr', True);
			ViewVar('db_tables', False);
			break;
			
		case 'CON':
		default:
			ViewVar('db_con', False);
			ViewVar('db_usr', False);
			ViewVar('db_tables', False);
			break;
		}
	}
	else {
		ViewVar('db_con', False);
		ViewVar('db_usr', False);
		ViewVar('db_tables', False);
	}
}
else {
	ViewVar('db_con', False);
	ViewVar('db_usr', False);
	ViewVar('db_tables', False);
}


