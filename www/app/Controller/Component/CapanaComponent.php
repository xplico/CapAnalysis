<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */
class CapanaComponent extends Component {
	 public function check() {
		/* check capanalysis status */
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
		if ($running == 0)
			return False;

		/* check DB */
		$running = 0;
		$ROOT_DIR = Configure::read('Dataset.root').'/';
		if (file_exists($ROOT_DIR.'/tmp/db.stat')) {
			$db = file($ROOT_DIR.'/tmp/db.stat');
			if (count($db) != 0) {
				$db[0] = rtrim($db[0], "\n");
				if ($db[0] == 'OK')
					$running = 1;
			}
		}
		if ($running == 0)
			return False;

		return True;
	}
}
