<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */
class StringComponent extends Component {
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
							$data = round($data, 1).'PB';
						}
						else {
							$data = round($data, 1).'TB';
						}
					}
					else {
						$data = round($data, 1).'GB';
					}
				}
				else {
					$data = round($data, 1).'MB';
				}
			}
			else {
				$data = round($data, 1).'KB';
			}
		}
		else {
			if ($data != 0)
				$data = $data.'B';
		}

        return $data;
    }
    public function toByteSize($p_sFormatted) {
        $aUnits = array('B'=>0, 'K'=>1, 'M'=>2, 'G'=>3, 'T'=>4, 'P'=>5, 'E'=>6, 'Z'=>7, 'Y'=>8);
        $sUnit = strtoupper(trim(substr($p_sFormatted, -1)));
        if (intval($sUnit) !== 0) {
            $sUnit = 'B';
        }
        if (!in_array($sUnit, array_keys($aUnits))) {
            return false;
        }
        $iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 1));
        if (!intval($iUnits) == $iUnits) {
            return false;
        }
        return $iUnits * pow(1024, $aUnits[$sUnit]);
    }
}
