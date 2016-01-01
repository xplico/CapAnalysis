<?php

if ($_SERVER["SERVER_PORT"] != "80")
	$url = 'http://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].$ROOT_APP;
else
	$url = 'http://'.$_SERVER["SERVER_NAME"].$ROOT_APP;

/* send commmand */
$mkdb = $ROOT_DIR.'/tmp/db.mk';
fclose(fopen($mkdb, 'w+'));

// wait
$secs = 180;
while (file_exists($mkdb)) {
	sleep(1);
	$secs--;
	if (!$secs)
		break;
}
if (!$secs)
	unlink($mkdb);

header('Location: '.$url);
