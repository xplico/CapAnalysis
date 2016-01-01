<?php


App::uses('Helper', 'View');

class StringHelper extends Helper {
	public function size($data = 0) {
		if ($data > 1024) {
			$data = $data/1024;
			if ($data > 1024) {
				$data = $data/1024;
				if ($data > 1024) {
					$data = $data/1024;
					if ($data > 1024) {
						$data = $data/1024;
						if ($data > 1024) {
							$data = $data/1024;
							$data = round($data, 1).'P';
						}
						else {
							$data = round($data, 1).'T';
						}
					}
					else {
						$data = round($data, 1).'G';
					}
				}
				else {
					$data = round($data, 1).' M';
				}
			}
			else {
				$data = round($data, 1).' K';
			}
		}

		return $data;
    }
    
	public function num($data = 0) {
		if ($data > 1000) {
			$data = $data/1000;
			if ($data > 1000) {
				$data = $data/1000;
				if ($data > 1000) {
					$data = $data/1000;
					$data = round($data, 1).'G';
				}
				else {
					$data = round($data, 1).'M';
				}
			}
			else {
				$data = round($data, 1).'K';
			}
		}

		return $data;
    }
}
