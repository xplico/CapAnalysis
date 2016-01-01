<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */
class CfgComponent extends Component {
	public $components = array('Session');

	private function net2flt($key, $data) {
		if ($key == 'is') { // ip source
			$res = array('Item.ip_src' => $data);
		}
		if ($key == 'id') { // ip destination
			$res = array('Item.ip_dst' => $data);
		}
		if ($key == 'nd') { // name destination
			$res = array('Item.dns LIKE' => '%'.$data.'%');
		}
		if ($key == 'ps') { // port source
			$res = array('Item.port_src' => $data);
		}
		if ($key == 'pd') { // port destination
			$res = array('Item.port_dst' => $data);
		}
		return $res;
	}

	private function dtime2flt($key, $data) {
		if ($key == 'de') { // date equal
			$res = array('Item.cdate' => $data);
		}
		if ($key == 'df') { // date from
			$res = array('Item.cdate >=' => $data);
		}
		if ($key == 'dt') { // date to
			$res = array('Item.cdate <=' => $data);
		}
		if ($key == 'tf') { // time from
			$res = array('Item.ctime >=' => $data);
		}
		if ($key == 'tt') { // time to
			$res = array('Item.ctime <=' => $data);
		}
		return $res;
	}

	private function data2flt($key, $op, $data=null) {
		if ($key == 'ds') { // sent
			switch ($op) {
			case '0':
				$res = array('Item.bsent >' => $data);
				break;
			case '1':
				$res = array('Item.bsent' => $data);
				break;
			case '2':
				$res = array('Item.bsent <' => $data);
				break;
			}
		}
		if ($key == 'dr') { // received
			switch ($op) {
			case '0':
				$res = array('Item.brecv >' => $data);
				break;
			case '1':
				$res = array('Item.brecv' => $data);
				break;
			case '2':
				$res = array('Item.brecv <' => $data);
				break;
			}
		}
		if ($key == 'dsr') { // sent vs received
			switch ($op) {
			case '0':
				$res = array('Item.bsent > Item.brecv');
				break;
			case '1':
				$res = array('Item.bsent = Item.brecv');
				break;
			case '2':
				$res = array('Item.bsent < Item.brecv');
				break;
			}
		}
		return $res;
	}
	
	public function columns() {
		$cols = array(
			__('Info'),
			__('Date & Time'),
			__('Classification'),
			__('Source IP'),
			__('Destination IP'),
			__('Destination Name'),
			__('Notes'),
			__('Source Port'),
			__('Destination Port'),
			__('L4'),
			__('Protocol'),
			__('Country'),
			__('Bytes Sent'),
			__('Bytes Received'),
			__('Bytes %'),
			__('Lost bytes Sent'),
			__('Lost bytes Received'),
			__('Packets Sent'),
			__('Packets Received'),
			__('Packets %'),
			__('Duration')
		);
		return $cols;
	}

	public function columns_en($colsen = null) {
		if ($colsen) {
			$this->Session->write('colsen', $colsen);
		}
		else {
			if ($this->Session->check('colsen')) {
				$colsen = $this->Session->read('colsen');
			}
			else {
				$colsen = range(0, 20, 1); // the number of columns
			}
		}
		return $colsen;
	}
	
	public function clean() {
		$this->Session->delete('capfile');
		$this->Session->delete('capfile_flt');
		$this->Session->delete('proto');
		$this->Session->delete('proto_flt');
		$this->Session->delete('country');
		$this->Session->delete('country_flt');
		$this->Session->delete('net');
		$this->Session->delete('net_flt');
		$this->Session->delete('dtime');
		$this->Session->delete('dtime_flt');
		$this->Session->delete('data');
		$this->Session->delete('data_flt');
	}
	
	public function active() {
		$act = array();
		$act[0] = $this->Session->check('capfile');
		$act[2] = $this->Session->check('proto');
		$act[3] = $this->Session->check('country');
		$act[1] = $this->Session->check('net');
		$act[5] = $this->Session->check('dtime');
		$act[4] = $this->Session->check('data');
		return $act;
	}
	
	public function capfiles($data = null, $save=false) {
		if ($data == null) {
			$this->Session->delete('capfile');
			$this->Session->delete('capfile_flt');
			return null;
		}
		if ($save) {
			if (count($data) == 0)
				$this->Session->delete('capfile');
			else
				$this->Session->write('capfile', $data);
		}
		else {
			if ($this->Session->check('capfile')) {
				$data = $this->Session->read('capfile');
			}
			else {
				$data = array_keys($data);
			}
		}
		return $data;
	}
	
	public function capfiles_flt($flt=null) {
		if ($flt) {
			$this->Session->write('capfile_flt', $flt);
		}
		else {
			$flt = $this->Session->read('capfile_flt');
			return $flt;
		}
		return null;
	}
	
	public function capfiles_act() {
		return $this->Session->check('capfile');
	}

	public function proto($data = null, $save=false) {
		if ($data == null) {
			$this->Session->delete('proto');
			$this->Session->delete('proto_flt');
			return null;
		}
		if ($save) {
			if (count($data) == 0)
				$this->Session->delete('proto');
			else
				$this->Session->write('proto', $data);
		}
		else {
			if ($this->Session->check('proto')) {
				$data = $this->Session->read('proto');
			}
			else {
				$data = array_keys($data);
			}
		}
		return $data;
	}
	
	public function proto_flt($flt=null) {
		if ($flt) {
			$this->Session->write('proto_flt', $flt);
		}
		else {
			$flt = $this->Session->read('proto_flt');
			return $flt;
		}
		return null;
	}
	
	public function proto_act() {
		return $this->Session->check('proto');
	}

	public function country($data = null, $save=false) {
		if ($data == null) {
			$this->Session->delete('country');
			$this->Session->delete('country_flt');
			return null;
		}
		if ($save) {
			if (count($data) == 0)
				$this->Session->delete('country');
			else
				$this->Session->write('country', $data);
		}
		else {
			if ($this->Session->check('country')) {
				$data = $this->Session->read('country');
			}
			else {
				$data = array_keys($data);
			}
		}
		return $data;
	}
	
	public function country_flt($flt=null) {
		if ($flt) {
			$this->Session->write('country_flt', $flt);
		}
		else {
			$flt = $this->Session->read('country_flt');
			return $flt;
		}
		return null;
	}
	
	public function country_act() {
		return $this->Session->check('country');
	}

	public function net($type, $ips, $ipd, $ports, $portd, $named) {
		if ($this->Session->check('net')) {
			$net = $this->Session->read('net');
			$net_flt = $this->Session->read('net_flt');
		}
		else {
			$net = array(0 => array(), 1 => array());
			$net_flt = array(0 => array(), 1 => array());
		}
		
		if ($ips) {
			$ips = trim($ips, ' ');
			$new = array('is', $ips);
			$net[$type][] = $new;
			$net_flt[$type][] = $this->net2flt('is', $ips);
		}
		if ($ipd) {
			$ipd = trim($ipd, ' ');
			$new = array('id', $ipd);
			$net[$type][] = $new;
			$net_flt[$type][] = $this->net2flt('id', $ipd);
		}
		if ($named) {
			$named = trim($named, ' ');
			$new = array('nd', $named);
			$net[$type][] = $new;
			$net_flt[$type][] = $this->net2flt('nd', $named);
		}
		if ($ports) {
			$ports = trim($ports, ' ');
			$new = array('ps', $ports);
			$net[$type][] = $new;
			$net_flt[$type][] = $this->net2flt('ps', $ports);
		}
		if ($portd) {
			$portd = trim($portd, ' ');
			$new = array('pd', $portd);
			$net[$type][] = $new;
			$net_flt[$type][] = $this->net2flt('pd', $portd);
		}
		$this->Session->write('net', $net);
		$this->Session->write('net_flt', $net_flt);
	}
	
	public function net_flt($type) {
		$flt = $this->Session->read('net_flt');
		if ($type == 'OR') {
			return $flt[0];
		}
		return $flt[1];
	}
	
	public function net_list() {
		$netflt = null;
		if ($this->Session->check('net')) {
			$netflt = $this->Session->read('net');
		}
		return $netflt;
	}
	
	public function net_rm($tp, $id) {
		$net = $this->Session->read('net');
		unset($net[$tp][$id]);
		if (empty($net[0]) and empty($net[1])) {
			$this->Session->delete('net');
			$this->Session->delete('net_flt');
		}
		else {
			$net_flt = $this->Session->read('net_flt');
			unset($net_flt[$tp]);
			$net_flt[$tp] = array();
			foreach ($net[$tp] as $rl) {
				$net_flt[$tp][] = $this->net2flt($rl[0], $rl[1]);
			}
			$this->Session->write('net', $net);
			$this->Session->write('net_flt', $net_flt);
		}
	}
	
	public function net_act() {
		return $this->Session->check('net');
	}
	
	public function dtime($type, $dfrom, $dto, $tfrom, $tto) {
		if ($this->Session->check('dtime')) {
			$dtime = $this->Session->read('dtime');
			$dtime_flt = $this->Session->read('dtime_flt');
		}
		else {
			$dtime = array(0 => array(), 1 => array());
			$dtime_flt = array(0 => array(), 1 => array());
		}
		if ($dfrom && $dto && $dto == $dfrom) {
			$new = array('de', $dfrom);
			$dtime[$type][] = $new;
			$dtime_flt[$type][] = $this->dtime2flt('de', $dfrom);
			$dfrom = null;
			$dto = null;
		}
		if ($dfrom) {
			$new = array('df', $dfrom);
			$dtime[$type][] = $new;
			$dtime_flt[$type][] = $this->dtime2flt('df', $dfrom);
		}
		if ($dto) {
			$new = array('dt', $dto);
			$dtime[$type][] = $new;
			$dtime_flt[$type][] = $this->dtime2flt('dt', $dto);
		}
		if ($tfrom) {
			$new = array('tf', $tfrom);
			$dtime[$type][] = $new;
			$dtime_flt[$type][] = $this->dtime2flt('tf', $tfrom);
		}
		if ($tto) {
			$new = array('tt', $tto);
			$dtime[$type][] = $new;
			$dtime_flt[$type][] = $this->dtime2flt('tt', $tto);
		}
		$this->Session->write('dtime', $dtime);
		$this->Session->write('dtime_flt', $dtime_flt);
	}
	
	public function dtime_flt($type) {
		$flt = $this->Session->read('dtime_flt');
		if ($type == 'OR') {
			return $flt[0];
		}
		return $flt[1];
	}
	
	public function dtime_list() {
		$dtimeflt = null;
		if ($this->Session->check('dtime')) {
			$dtimeflt = $this->Session->read('dtime');
		}
		return $dtimeflt;
	}
	
	public function dtime_rm($tp, $id) {
		$dtime = $this->Session->read('dtime');
		unset($dtime[$tp][$id]);
		if (empty($dtime[0]) and empty($dtime[1])) {
			$this->Session->delete('dtime');
			$this->Session->delete('dtime_flt');
		}
		else {
			$dtime_flt = $this->Session->read('dtime_flt');
			unset($dtime_flt[$tp]);
			$dtime_flt[$tp] = array();
			foreach ($dtime[$tp] as $rl) {
				$dtime_flt[$tp][] = $this->dtime2flt($rl[0], $rl[1]);
			}
			$this->Session->write('dtime', $dtime);
			$this->Session->write('dtime_flt', $dtime_flt);
		}
	}
	
	public function dtime_act() {
		return $this->Session->check('dtime');
	}
	
	public function data($type, $dso, $dss, $dro, $drs, $dsr) {
		if ($this->Session->check('data')) {
			$data = $this->Session->read('data');
			$data_flt = $this->Session->read('data_flt');
		}
		else {
			$data = array(0 => array(), 1 => array());
			$data_flt = array(0 => array(), 1 => array());
		}
		
		if ($dss != null) {
			$new = array('ds', $dso, $dss);
			$data[$type][] = $new;
			$data_flt[$type][] = $this->data2flt('ds', $dso, $dss);
		}
		if ($drs != null) {
			$new = array('dr', $dro, $drs);
			$data[$type][] = $new;
			$data_flt[$type][] = $this->data2flt('dr', $dro, $drs);
		}
		if ($dsr != null) {
			$new = array('dsr', $dsr);
			$data[$type][] = $new;
			$data_flt[$type][] = $this->data2flt('dsr', $dsr);
		}
		$this->Session->write('data', $data);
		$this->Session->write('data_flt', $data_flt);
	}
	
	public function data_flt($type) {
		$flt = $this->Session->read('data_flt');
		if ($type == 'OR') {
			return $flt[0];
		}
		return $flt[1];
	}
	
	public function data_list() {
		$dataflt = null;
		if ($this->Session->check('data')) {
			$dataflt = $this->Session->read('data');
		}
		return $dataflt;
	}
	
	public function data_rm($tp, $id) {
		$data = $this->Session->read('data');
		unset($data[$tp][$id]);
		if (empty($data[0]) and empty($data[1])) {
			$this->Session->delete('data');
			$this->Session->delete('data_flt');
		}
		else {
			$data_flt = $this->Session->read('data_flt');
			unset($data_flt[$tp]);
			$data_flt[$tp] = array();
			foreach ($data[$tp] as $rl) {
				$data_flt[$tp][] = $this->data2flt($rl[0], $rl[1], $rl[2]);
			}
			$this->Session->write('data', $data);
			$this->Session->write('data_flt', $data_flt);
		}
	}
	
	public function data_act() {
		return $this->Session->check('data');
	}
}
