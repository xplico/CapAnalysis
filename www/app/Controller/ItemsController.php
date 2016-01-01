<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('Sanitize', 'Utility');
App::uses('AppController', 'Controller');

class ItemsController extends AppController {
	public $components = array('Cfg', 'Session', 'Item2Pcap', 'RequestHandler');
	public $helpers = array('String');
	var $limit = 13;
	var $paginate = array('limit' => 13);
	var $group_id = 0;
	var $dataset_id = 0;
	
	private function removeCacheFiles() {
		$paths = array(APP . 'tmp' . DS . 'cache/models',
				APP . 'tmp' . DS . 'cache/persistent',
				APP . 'tmp' . DS . 'cache/views'
				);
		foreach ($paths as $path) {
			$files = scandir($path);
			foreach ($files as $file) {
				if (strstr($file, 'capanal_cake_') != FALSE) {
					@unlink($path . DS . $file);
				}
			}
		}
	}

	private function Conditions() {
		$conditions = array();
		if ($this->Cfg->capfiles_act()) { // capture files
			if (isset($conditions['AND']))
				$conditions['AND'][] = array('OR' => $this->Cfg->capfiles_flt());
			else
				$conditions['AND'] = array(array('OR' => $this->Cfg->capfiles_flt()));
		}
		if ($this->Cfg->proto_act()) { // protocols
			if (isset($conditions['AND']))
				$conditions['AND'][] = array('OR' => $this->Cfg->proto_flt());
			else
				$conditions['AND'] = array(array('OR' => $this->Cfg->proto_flt()));
		}
		if ($this->Cfg->country_act()) { // protocols
			if (isset($conditions['AND']))
				$conditions['AND'][] = array('OR' => $this->Cfg->country_flt());
			else
				$conditions['AND'] = array(array('OR' => $this->Cfg->country_flt()));
		}
		if ($this->Cfg->net_act()) { // ip, port
			$add = $this->Cfg->net_flt('OR');
			if (!empty($add)) {
				if (!isset($conditions['OR'])) {
					$conditions['OR'] = array();
				}
				$conditions['OR'] = array_merge($conditions['OR'], $add);
			}
			$add = $this->Cfg->net_flt('AND');
			if (!empty($add)) {
				if (!isset($conditions['AND'])) {
					$conditions['AND'] = array();
				}
				$conditions['AND'] = array_merge($conditions['AND'], $add);
			}
		}
		if ($this->Cfg->dtime_act()) { // date time
			$add = $this->Cfg->dtime_flt('OR');
			if (!empty($add)) {
				if (!isset($conditions['OR'])) {
					$conditions['OR'] = array();
				}
				$conditions['OR'] = array_merge($conditions['OR'], $add);
			}
			$add = $this->Cfg->dtime_flt('AND');
			if (!empty($add)) {
				if (!isset($conditions['AND'])) {
					$conditions['AND'] = array();
				}
				$conditions['AND'] = array_merge($conditions['AND'], $add);
			}
		}
		if ($this->Cfg->data_act()) { // data size
			$add = $this->Cfg->data_flt('OR');
			if (!empty($add)) {
				if (!isset($conditions['OR'])) {
					$conditions['OR'] = array();
				}
				$conditions['OR'] = array_merge($conditions['OR'], $add);
			}
			$add = $this->Cfg->data_flt('AND');
			if (!empty($add)) {
				if (!isset($conditions['AND'])) {
					$conditions['AND'] = array();
				}
				$conditions['AND'] = array_merge($conditions['AND'], $add);
			}
		}
		//print_r($conditions);
		
		return $conditions;
	}
	
	public function beforeFilter() {
		parent::beforeFilter();
		if ($this->Session->check('group_id')) {
			$this->group_id = $this->Session->read('group_id');
		}
		else {
			die();
		}
		if ($this->Session->check('dataset_id')) {
			$this->dataset_id = $this->Session->read('dataset_id');
			$this->Item->setSource('items_'.$this->dataset_id);
		}
		if ($this->Session->check('limit_row')) {
			$this->limit = $this->Session->read('limit_row');
		}
	}
	
	public function index($id = null) {
		if ($id != null) {
			$this->Item->Dataset->recursive = -1;
			$this->Item->Dataset->id = $id;
			if (!$this->Item->Dataset->exists()) {
				$this->redirect(array('controller' => 'users', 'action' => 'login'));
				die();
			}
			if ($this->Session->check('demo')) {
				$params['conditions'] = array('Dataset.group_id' => $this->group_id, 'Dataset.name' => 'Set '.$this->Session->read('ip_usr'), 'Dataset.id' => $id);
				$ds_count = $this->Item->Dataset->find('count', $params);
				if (!$ds_count) {
					$this->Session->setFlash(__('Oops...'));
					$this->redirect(array('controller' => 'users', 'action' => 'login'));
					die();
				}
			}
			if ($this->dataset_id != $id) {
				$this->Cfg->clean();
				$this->Session->delete('items_max');
				Cache::clear();
				clearCache();
				$this->removeCacheFiles();
				Cache::clear();
				clearCache();
				$db = ConnectionManager::getDataSource('default');
				$tables = $db->listSources();
				if (!in_array('items_'.$id, $tables)) {
					$this->Session->setFlash(__('DataSet creation in progress... wait some seconds!'));
					$this->redirect(array('controller' => 'users', 'action' => 'login'));
					return;
				}
			}
			$ds = $this->Item->Dataset->read();
			$this->dataset_id = $id;
			$this->Session->write('dataset_id', $id);
			$this->Session->write('dataset_name', $ds['Dataset']['name']);
			$this->Item->setSource('items_'.$id);
			$this->set('dataset_nm', $ds['Dataset']['name']);
			$this->set('iid', $this->dataset_id);
		}
		else {
            if ($this->dataset_id == 0) {
				$this->redirect(array('controller' => 'users', 'action' => 'login'));
				die();
            }
                
			$this->Item->Dataset->recursive = -1;
			$this->Item->Dataset->id = $this->dataset_id;
			$ds = $this->Item->Dataset->read();
			if ($ds === false) {
				$this->redirect(array('controller' => 'users', 'action' => 'login'));
				die();
			}
			$this->set('dataset_nm', $ds['Dataset']['name']);
			$this->set('iid', $this->dataset_id);
		}
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$items_num = $this->Item->find('count', array('conditions' => $conditions));
		$ips_num = $this->Item->find('count', array('conditions' => $conditions, 'group' => 'Item.ips_id'));
		$ipd_num = $this->Item->find('count', array('conditions' => $conditions, 'group' => 'Item.ipd_id'));
		if (!$this->Session->check('items_max'))
			$this->Session->write('items_max', $items_num);
		$this->set('items_num', $items_num);
		$this->set('ips_num', $ips_num);
		$this->set('ipd_num', $ipd_num);
		$this->set('cfgf', $this->Cfg->active());// filters active
	}

	public function items() {
		if ($this->dataset_id == 0)
			die();
		$this->layout = 'none';
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		//print_r($conditions);
		
		$this->paginate = array('Item' => array(
			'fields' => array('Item.id', 'Item.cdate', 'Item.ctime', 'Item.classification_id', 'Item.ip_src', 'Item.ip_dst', 'Item.ips_id', 'Item.ipd_id', 'Item.dns', 'Item.port_src', 'Item.port_dst', 'Item.l4prot', 'Item.l7prot', 'Item.country', 'Item.bsent', 'Item.brecv', 'Item.blsent', 'Item.blrecv', 'Item.pktsent', 'Item.pktrecv', 'Item.duration'),
			'order' => array('Item.cdate' => 'DESC', 'Item.ctime' => 'DESC'),
			'conditions' => $conditions,
			'limit' => $this->limit
		));

		$columns_name = $this->Cfg->columns();
		$columns_en = $this->Cfg->columns_en();
		$columns = array();
		$columns = array_pad($columns, count($columns_name), false);
		foreach ($columns_en as $en)
			$columns[$en] = true;
		
		//$items_num = $this->Item->find('count', array('conditions' => $conditions));
		$this->set('items', $this->paginate('Item'));
		$this->set('columns', $columns);
	}

	public function info($id = null) {
		$this->layout = 'none';
		$this->Item->id = $id;
		if (!$this->Item->exists()) {
			throw new NotFoundException(__('Invalid item'));
		}
		$this->Item->recursive = -1;
		$item = $this->Item->read(array('dataset_id', 'capfile_id', 'ip_src', 'ip_dst', 'port_src', 'port_dst', 'encaps', 'l4prot'), $id);
		if ($item['Item']['dataset_id'] == $this->dataset_id) {
			$info = $item;
			$this->Item->Capfile->recursive = -1;
			$this->Item->Capfile->id = $item['Item']['capfile_id'];
			$file = $this->Item->Capfile->read();
			$this->set('filename', $file['Capfile']['filename']);
		}
		else {
			$this->Session->setFlash(__('Wrong request'));
			$info = __('No Data');
		}
		$this->set('info', $info);
		$this->set('id', $id);
	}

	public function ipdata($ip_id = null) {
		if ($ip_id == null)
			die();
		
		$this->layout = 'none';
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$con = $conditions;
		if (!isset($conditions['AND'])) {
			$con['AND'] = array();
		}
		// IP as a source
		$con['AND'][] = array('Item.ips_id' => $ip_id);
		$fist_s_con = $this->Item->find('first', array(
				'fields' => array('Item.cdate', 'Item.ctime'),
				'conditions' => $con,
				'order' => array('Item.cdate' => 'ASC', 'Item.ctime' => 'ASC')
		));
		$last_s_con = $this->Item->find('first', array(
				'fields' => array('Item.cdate', 'Item.ctime'),
				'conditions' => $con,
				'order' => array('Item.cdate' => 'DESC', 'Item.ctime' => 'DESC')
		));
		$flows_s_cnt = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin'),
				'conditions' => $con
		));
		$ipd_cnt = $this->Item->find('count', array(
				'fields' => array('Item.ipd_id'),
				'conditions' => $con,
				'group' => 'Item.ipd_id'
		));
		$flows_cnt = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin'),
				'conditions' => $conditions
		));
		if ($fist_s_con == null) {
			$fist_s_con['Item']['cdate'] = __('None');
			$fist_s_con['Item']['ctime'] = '';
			$last_s_con['Item']['cdate']= __('None');
			$last_s_con['Item']['ctime'] = '';
			$flows_s_cnt[0][0]['fcnt'] = 0;
			$flows_s_cnt[0][0]['bin'] = 0;
			$flows_s_cnt[0][0]['bout'] = 0;
			$flows_cnt[0][0]['fcnt'] = 0;
			$flows_cnt[0][0]['bin'] = 0;
			$flows_cnt[0][0]['bout'] = 0;
		}
		$this->set('fist_s_con', $fist_s_con);
		$this->set('last_s_con', $last_s_con);
		$this->set('flows_s_cnt', $flows_s_cnt);
		$this->set('flows_cnt', $flows_cnt);
		$this->set('ipd_cnt', $ipd_cnt);
		$this->set('ip_id', $ip_id);
	}

	public function ipdatad($ip_id = null) {
		if ($ip_id == null)
			die();
		
		$this->layout = 'none';
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$con = $conditions;
		if (!isset($conditions['AND'])) {
			$con['AND'] = array();
		}
		// IP as a source
		$con['AND'][] = array('Item.ipd_id' => $ip_id);
		$first_d_con = $this->Item->find('first', array(
				'fields' => array('Item.cdate', 'Item.ctime'),
				'conditions' => $con,
				'order' => array('Item.cdate' => 'ASC', 'Item.ctime' => 'ASC')
		));
		$last_d_con = $this->Item->find('first', array(
				'fields' => array('Item.cdate', 'Item.ctime'),
				'conditions' => $con,
				'order' => array('Item.cdate' => 'DESC', 'Item.ctime' => 'DESC')
		));
		$flows_d_cnt = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bin', 'SUM(Item.brecv) as bout'),
				'conditions' => $con
		));
		$ips_cnt = $this->Item->find('count', array(
				'fields' => array('Item.ips_id'),
				'conditions' => $con,
				'group' => 'Item.ips_id'
		));
		$flows_cnt = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bin', 'SUM(Item.brecv) as bout'),
				'conditions' => $conditions
		));
		if ($first_d_con == null) {
			$first_d_con['Item']['cdate'] = __('None');
			$first_d_con['Item']['ctime'] = '';
			$last_d_con['Item']['cdate']= __('None');
			$last_d_con['Item']['ctime'] = '';
			$flows_d_cnt[0][0]['fcnt'] = 0;
			$flows_d_cnt[0][0]['bin'] = 0;
			$flows_d_cnt[0][0]['bout'] = 0;
			$flows_cnt[0][0]['fcnt'] = 0;
			$flows_cnt[0][0]['bin'] = 0;
			$flows_cnt[0][0]['bout'] = 0;
		}
		$this->set('fist_d_con', $first_d_con);
		$this->set('last_d_con', $last_d_con);
		$this->set('flows_d_cnt', $flows_d_cnt);
		$this->set('flows_cnt', $flows_cnt);
		$this->set('ips_cnt', $ips_cnt);
		$this->set('ip_id', $ip_id);
	}
	
	public function ipdjsn($source = null, $ip_id = null, $name = null) {
		if ($source == null || $name == null || $ip_id == null)
			die();
		
		$this->layout = 'none';
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$con = $conditions;
		if (!isset($conditions['AND'])) {
			$con['AND'] = array();
		}
		// IP as a source or ad destination
		if ($source == 'src')
			$con['AND'][] = array('Item.ips_id' => $ip_id);
		else
			$con['AND'][] = array('Item.ipd_id' => $ip_id);
		$data = array('f' => array(), 'i' => array(), 'o' => array(), 't' => array());
		if ($name == 'sprot') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin'),
				'group' => array('Item.l7prot'),
				'conditions' => $con,
			));
			foreach ($ips as $ip) {
				$data['f'][] = array('n' => $ip['Item']['l7prot'], 'val' => $ip[0]['fcnt']);
				$data['i'][] = array('n' => $ip['Item']['l7prot'], 'val' => $ip[0]['bin']);
				$data['o'][] = array('n' => $ip['Item']['l7prot'], 'val' => $ip[0]['bout']);
				$data['t'][] = array('n' => $ip['Item']['l7prot'], 'val' => $ip[0]['bin'] + $ip[0]['bout']);
			}
		}
		else if ($name == 'cntr') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.country', 'COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin'),
				'group' => array('Item.country'),
				'conditions' => $con,
			));
			foreach ($ips as $ip) {
				$data['f'][] = array('n' => $ip['Item']['country'], 'val' => $ip[0]['fcnt']);
				$data['i'][] = array('n' => $ip['Item']['country'], 'val' => $ip[0]['bin']);
				$data['o'][] = array('n' => $ip['Item']['country'], 'val' => $ip[0]['bout']);
				$data['t'][] = array('n' => $ip['Item']['country'], 'val' => $ip[0]['bin'] + $ip[0]['bout']);
			}
		}
		else if ($name == 'hours') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.hour', 'COUNT(Item.id) as fcnt', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin'),
				'group' => array('Item.hour'),
				'conditions' => $con,
			));
			foreach ($ips as $ip) {
				$data['f'][] = array('x' => $ip['Item']['hour'], 'y' => $ip[0]['fcnt']);
				$data['i'][] = array('x' => $ip['Item']['hour'], 'y' => $ip[0]['bin']);
				$data['o'][] = array('x' => $ip['Item']['hour'], 'y' => $ip[0]['bout']);
				$data['t'][] = array('x' => $ip['Item']['hour'], 'y' => $ip[0]['bin'] + $ip[0]['bout']);
			}
		}
		else if ($name == 'names') {
			$data = array();
			$nms = $this->Item->find('all', array(
				'fields' => array('Item.dns'),
				'group' => array('Item.dns'),
				'conditions' => $con,
			));
			foreach ($nms as $nm) {
				$data[] = $nm['Item']['dns'];
			}
		}
		$this->set('data', $data);
		$this->set('_serialize', 'data');
	}

	public function connect($ip_id = null) {
		if ($ip_id == null)
			die();
		
		$this->layout = 'none';
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$con = $conditions;
		if (!isset($conditions['AND'])) {
			$con['AND'] = array();
		}
		$con['AND'][] = array('Item.ips_id' => $ip_id);
		$prot_cntr = $this->Item->find('all', array(
			'fields' => array('COUNT(Item.id) as cnt', 'SUM(Item.brecv) as datar', 'SUM(Item.bsent) as datas', 'Item.country', 'Item.ipd'),
			'group' => array('Item.country', 'Item.ipd_id'),
			'conditions' => $con
		));
		$cntry = array();
		$tot = array();
		$tot['ip'] = array('f' => 0, 'r' => 0, 's' => 0, 'ip' => 0);
		foreach ($prot_cntr as $prt) {
			$c = $prt['Item']['country'];
			$cntry[$c][] = array('name' => $prt['Item']['ipd'], 'f' => $prt[0]['cnt'], 'r' => $prt[0]['datar'], 's' => $prt[0]['datas']);
			if (isset($tot[$c])) {
				$tot[$c]['f'] += $prt[0]['cnt'];
				$tot[$c]['r'] += $prt[0]['datar'];
				$tot[$c]['s'] += $prt[0]['datas'];
				$tot[$c]['ip']++;
			}
			else {
				$tot[$c]['f'] = $prt[0]['cnt'];
				$tot[$c]['r'] = $prt[0]['datar'];
				$tot[$c]['s'] = $prt[0]['datas'];
				$tot[$c]['ip'] = 1;
			}
			$tot['ip']['f'] += $prt[0]['cnt'];
			$tot['ip']['r'] += $prt[0]['datar'];
			$tot['ip']['s'] += $prt[0]['datas'];
			$tot['ip']['ip']++;
		}
		$cntryn = array();
		foreach ($cntry as $key => $cnt) {
			$cntryn[] = array('name' => $key, 'children' => $cnt, 'f' => $tot[$key]['f'], 'r' => $tot[$key]['r'], 's' => $tot[$key]['s'], 'ip' => $tot[$key]['ip']);
		}
		unset($cntry);
		$nodes = array('name' => 'IP', 'children' => $cntryn, 'f' => $tot['ip']['f'], 'r' => $tot['ip']['r'], 's' => $tot['ip']['s'], 'ip' => $tot['ip']['ip']);
		$this->set('prot_cntrs', json_encode($nodes));
	}
	
	public function whois($ip_id = null) {
		if ($ip_id == null)
			die();
			
		$this->Item->recursive = -1;		
		$ip = $this->Item->find('first', array(
			'fields' => array('Item.ip_src'),
			'conditions' => array('Item.ips_id' => $ip_id),
		));
		if ($ip != null) {
			$ip = $ip['Item']['ip_src'];
		}
		else {
			$ip = $this->Item->find('first', array(
				'fields' => array('Item.ip_dst'),
				'conditions' => array('Item.ipd_id' => $ip_id),
			));
			$ip = $ip['Item']['ip_dst'];
		}
	
		$this->layout = 'none';
		
		$olines = array($ip);
		exec('whois '.$ip, $olines);
		$this->set('whois', $olines);
	}

	public function pcap($id=null) {
		$this->layout = 'none';
		$this->Item->recursive = -1;
		$item = $this->Item->read(array('dataset_id', 'capfile_id', 'ip_src', 'ip_dst', 'port_src', 'port_dst', 'encaps', 'l4prot'), $id);
		$file_pcap = '/tmp/item_'.time().'_'.$id.'.pcap';
		$this->Item2Pcap->doPcap($file_pcap, $item['Item']);
		$this->autoRender = false;
		$this->disableCache();
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Expires: 0');
		header('Content-Disposition: filename=item_'.$id.'.pcap');
		header('Content-Length: ' . filesize($file_pcap));
		@readfile($file_pcap);
		unlink($file_pcap);
		exit();
	}

	public function viewcfg() {
		$this->layout = 'menutab';
		if ($this->request->is('post')) {
			$this->Session->setFlash(__('Columns selected'));
			$this->Cfg->columns_en($this->request->data['Item']['columns']);
			if ($this->request->data['Item']['limit_row'] != $this->limit && $this->request->data['Item']['limit_row'] != 0) {
				if (is_numeric($this->request->data['Item']['limit_row'])) {
					if ($this->request->data['Item']['limit_row'] > 100)
						$this->limit = 100;
					else
						$this->limit = $this->request->data['Item']['limit_row'];
					$this->Session->write('limit_row', $this->limit);
				}
			}
			$this->redirect(array('action' => 'index'));
		}
		$this->set('fields', $this->Cfg->columns());
		$this->set('selected', $this->Cfg->columns_en());
		$this->set('limit_row', $this->limit);
		$this->disableCache();
	}
	
	public function filecfg() {
		$this->layout = 'menutab';
		$this->Item->Capfile->recursive = -1;
		$conditions = array('Capfile.dataset_id' => $this->dataset_id);
		$capfiles = $this->Item->Capfile->find('list', array(
			'fields' => array('Capfile.filename'),
			'order' => array('Capfile.filename ASC'),
			'conditions' => $conditions
		));
		if ($this->request->is('post')) {
			if ($this->Item->Capfile->find('count', array('conditions' => $conditions)) != count($this->request->data['Item']['capfiles'])) {
				if (!empty($this->request->data['Item']['capfiles'])) {
					$this->Cfg->capfiles($this->request->data['Item']['capfiles'], true);
					$flt = array();
					foreach($this->request->data['Item']['capfiles'] as $elem) {
						$flt[] = array('Item.capfile_id' => $elem);
					}
					$this->Cfg->capfiles_flt($flt);
				}
			}
			else
				$this->Cfg->capfiles();
			$this->Session->setFlash(__('Files selected'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('fields', $capfiles);
		$this->set('selected', $this->Cfg->capfiles($capfiles));
		$this->disableCache();
	}
	
	public function protocfg() {
		$this->layout = 'menutab';
		$this->Item->recursive = -1;
		/*
		$proto = $this->Item->find('list', array(
			'fields' => array('DISTINCT Item.l7prot', 'Item.id'),
			'order' => array('Item.l7prot ASC'),
			'conditions' => $conditions
		));
		*/
		
		$proto = $this->Item->find('list', array(
			'fields' => array('Item.l7prot', 'Item.l7prot'),
			'order' => array('Item.l7prot ASC'),
			'group' => array('Item.l7prot')
		));
		if ($this->request->is('post')) {
			$this->Session->setFlash(__('Protocols filtered'));
			if (count($proto) != count($this->request->data['Item']['proto'])) {
				if (!empty($this->request->data['Item']['proto'])) {
					$this->Cfg->proto($this->request->data['Item']['proto'], true);
					$flt = array();
					foreach($this->request->data['Item']['proto'] as $elem) {
						$flt[] = array('Item.l7prot' => $elem);
					}
					$this->Cfg->proto_flt($flt);
				}
			}
			else
				$this->Cfg->proto();
			$this->redirect(array('action' => 'index'));
		}
		foreach ($proto as $key => $prot)
			$proto[$key] = $key;
		$this->set('fields', $proto);
		$this->set('selected', $this->Cfg->proto($proto));
		$this->disableCache();
	}

	public function countrycfg() {
		$this->layout = 'menutab';
		$this->Item->recursive = -1;
		/*
		$proto = $this->Item->find('list', array(
			'fields' => array('DISTINCT Item.country', 'Item.id'),
			'order' => array('Item.country ASC'),
			'conditions' => $conditions
		));
		*/
		
		$country = $this->Item->find('list', array(
			'fields' => array('Item.country', 'Item.country'),
			'order' => array('Item.country ASC'),
			'group' => array('Item.country')
		));
		
		if ($this->request->is('post')) {
			$this->Session->setFlash(__('Country filtered'));
			if (count($country) != count($this->request->data['Item']['country'])) {
				if (!empty($this->request->data['Item']['country'])) {
					$this->Cfg->country($this->request->data['Item']['country'], true);
					$flt = array();
					foreach($this->request->data['Item']['country'] as $elem) {
						$flt[] = array('Item.country' => $elem);
					}
					$this->Cfg->country_flt($flt);
				}
			}
			else
				$this->Cfg->country();
			$this->redirect(array('action' => 'index'));
		}
		foreach ($country as $key => $cnt)
			$country[$key] = $key;
		$this->set('fields', $country);
		$this->set('selected', $this->Cfg->country($country));
		$this->disableCache();
	}
	
	public function netcfg() {
		$this->layout = 'menutab';
		$this->Item->recursive = -1;
		if ($this->request->is('post')) {
			$type = $this->request->data['Item']['type'];
			$ips = $ipd = $named = $ports = $portd = null;
			if ($this->request->data['Item']['netsip']) {
				$ips = Sanitize::paranoid($this->request->data['Item']['netsip'], array('.'));
			}
			if ($this->request->data['Item']['netdip']) {
				$ipd = Sanitize::paranoid($this->request->data['Item']['netdip'], array('.'));
			}
			if ($this->request->data['Item']['netdname']) {
				$named = Sanitize::paranoid($this->request->data['Item']['netdname'], array('.'));
			}
			if ($this->request->data['Item']['netsport']) {
				$ports = Sanitize::paranoid($this->request->data['Item']['netsport'], array('.'));
			}
			if ($this->request->data['Item']['netdport']) {
				$portd = Sanitize::paranoid($this->request->data['Item']['netdport'], array('.'));
			}
			if ($ips or $ipd or $ports or $portd or $named) {
				$this->Session->setFlash(__('IP/Port filtered'));
				$this->Cfg->net($type, $ips, $ipd, $ports, $portd, $named);
			}
			$this->redirect(array('action' => 'index'));
		}
		/*
		$netsip = $this->Item->find('list', array(
			'fields' => array('DISTINCT Item.ip_src'),
			'order' => array('Item.ip_src ASC'),
			'conditions' => $conditions
		));
		*/
		$nrules = $this->Cfg->net_list();
		$this->set('nrules', $nrules);
		$this->disableCache();
	}

	public function netcfg_rm($tp, $id) {
		$nrules = $this->Cfg->net_rm($tp, $id);
		die();
	}

	public function timecfg() {
		$this->layout = 'menutab';
		$this->Item->recursive = -1;
		if ($this->request->is('post')) {
			$type = $this->request->data['Item']['type'];
			$dfrom = $dto = $tfrom = $tto = null;
			if ($this->request->data['Item']['dfrom']) {
				$dfrom = Sanitize::paranoid($this->request->data['Item']['dfrom'], array('-'));
			}
			if ($this->request->data['Item']['dto']) {
				$dto = Sanitize::paranoid($this->request->data['Item']['dto'], array('-'));
			}
			if ($this->request->data['Item']['tfrom'] and $this->request->data['Item']['tfrom'] != '00:00') {
				$tfrom = Sanitize::paranoid($this->request->data['Item']['tfrom'], array(':'));
			}
			if ($this->request->data['Item']['tto'] and $this->request->data['Item']['tto'] != '23:59') {
				$tto = Sanitize::paranoid($this->request->data['Item']['tto'], array(':'));
			}
			if ($dfrom or $dto or $tfrom or $tto) {
				$this->Session->setFlash(__('Date/Time filtered'));
				$this->Cfg->dtime($type, $dfrom, $dto, $tfrom, $tto);
			}
			$this->redirect(array('action' => 'index'));
		}
		
		$trules = $this->Cfg->dtime_list();
		$this->set('nrules', $trules);
		$this->disableCache();
	}

	public function timecfg_rm($tp, $id) {
		$nrules = $this->Cfg->dtime_rm($tp, $id);
		die();
	}

	public function datacfg() {
		$this->layout = 'menutab';
		$this->Item->recursive = -1;
		if ($this->request->is('post')) {
			$type = $this->request->data['Item']['type'];
			$dso = $dss = $dro = $drs = $dsr = null;
			if (is_numeric($this->request->data['Item']['dso'])) {
				$dso = Sanitize::paranoid($this->request->data['Item']['dso']);
				if (is_numeric($this->request->data['Item']['dss'])) {
					$dss = Sanitize::paranoid($this->request->data['Item']['dss']);
				}
			}
			if (is_numeric($this->request->data['Item']['dro'])) {
				$dro = Sanitize::paranoid($this->request->data['Item']['dro']);
				if (is_numeric($this->request->data['Item']['drs'])) {
					$drs = Sanitize::paranoid($this->request->data['Item']['drs']);
				}
			}
			if (is_numeric($this->request->data['Item']['dsr'])) {
				$dsr = Sanitize::paranoid($this->request->data['Item']['dsr']);
			}
			if ($dss !== null or $drs !== null or $dsr !== null) {
				$this->Session->setFlash(__('Data Size filtered'));
				$this->Cfg->data($type, $dso, $dss, $dro, $drs, $dsr);
			}
			$this->redirect(array('action' => 'index'));
		}
		
		$trules = $this->Cfg->data_list();
		$this->set('nrules', $trules);
		$this->disableCache();
	}

	public function datacfg_rm($tp, $id) {
		$nrules = $this->Cfg->data_rm($tp, $id);
		die();
	}

	public function statistics() {
		$this->layout = 'tab';
		$this->disableCache();
	}

	public function stat_ip($name=null) {
		/* top ips */
		if ($this->dataset_id == 0 || $name == null)
			die();
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$data = array();
		if ($name == 'ipsf') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.ip_src', 'COUNT(Item.id) as icnt'),
				'order' => array('icnt DESC'),
				'group' => array('Item.ip_src'),
				'conditions' => $conditions,
				'limit' => 10
			));
			foreach ($ips as $ip) {
				$data[] = array('name' => $ip['Item']['ip_src'], 'Flows' => $ip[0]['icnt']);
			}
		}
		else if ($name == 'ipsd') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.ip_src', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin', 'SUM("Item"."brecv"+"Item"."bsent") as btot'),
				'order' => array('btot DESC'),
				'group' => array('Item.ip_src'),
				'conditions' => $conditions,
				'limit' => 10
			));
			foreach ($ips as $ip) {
				$data[] = array('name' => $ip['Item']['ip_src'], 'Byte Sent' => $ip[0]['bout'], 'Byte Received' => $ip[0]['bin']);
			}
		}
		else if ($name == 'ipdf') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.ip_dst', 'COUNT(Item.id) as icnt'),
				'order' => array('icnt DESC'),
				'group' => array('Item.ip_dst'),
				'conditions' => $conditions,
				'limit' => 10
			));
			foreach ($ips as $ip) {
				$data[] = array('name' => $ip['Item']['ip_dst'], 'Flows' => $ip[0]['icnt']);
			}
		}
		else if ($name == 'ipdd') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.ip_dst', 'SUM(Item.bsent) as bout', 'SUM(Item.brecv) as bin', 'SUM("Item"."brecv"+"Item"."bsent") as btot'),
				'order' => array('btot DESC'),
				'group' => array('Item.ip_dst'),
				'conditions' => $conditions,
				'limit' => 10
			));
			foreach ($ips as $ip) {
				$data[] = array('name' => $ip['Item']['ip_dst'], 'Byte Sent' => $ip[0]['bout'], 'Byte Received' => $ip[0]['bin']);
			}
		}
		else if ($name == 'prot') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as tot'),
				'order' => array('tot DESC'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions,
				'limit' => 10
			));
			foreach ($ips as $ip) {
				$data[] = array('name' => $ip['Item']['l7prot'], 'Flows' => $ip[0]['tot']);
			}
		}
		else if ($name == 'country') {
			$ips = $this->Item->find('all', array(
				'fields' => array('Item.country', 'COUNT(Item.id) as tot'),
				'order' => array('tot DESC'),
				'group' => array('Item.country'),
				'conditions' => $conditions,
				'limit' => 10
			));
			foreach ($ips as $ip) {
				$data[] = array('name' => $ip['Item']['country'], 'Flows' => $ip[0]['tot']);
			}
		}
		else if ($name == 'duration') {
			$prots = $this->Item->find('all', array(
				'fields' => array('Item.l7prot'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$empty = array('name' => '');
			foreach($prots as $prot)
				$empty[$prot['Item']['l7prot']] = 0;
			unset($prots);
			
			if (!isset($conditions['AND'])) {
				$conditions['AND'] = array();
			}
			$conditions['AND'] += array('Item.duration <=' => 0);
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '1s';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 2;
			$conditions['AND']['Item.duration >'] = 1;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '2s';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 5;
			$conditions['AND']['Item.duration >'] = 2;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '5s';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 10;
			$conditions['AND']['Item.duration >'] = 5;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '10s';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 30;
			$conditions['AND']['Item.duration >'] = 10;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '30s';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 60;
			$conditions['AND']['Item.duration >'] = 30;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '1m';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 120;
			$conditions['AND']['Item.duration >'] = 60;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '2m';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 180;
			$conditions['AND']['Item.duration >'] = 120;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '3m';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 600;
			$conditions['AND']['Item.duration >'] = 180;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '10m';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 1800;
			$conditions['AND']['Item.duration >'] = 600;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '30m';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			$conditions['AND']['Item.duration <='] = 3600;
			$conditions['AND']['Item.duration >'] = 1800;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '1h';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
			
			unset($conditions['AND']['Item.duration <=']);
			$conditions['AND']['Item.duration >'] = 3600;
			$counts = $this->Item->find('all', array(
				'fields' => array('Item.l7prot', 'COUNT(Item.id) as num'),
				'group' => array('Item.l7prot'),
				'conditions' => $conditions
			));
			$new = $empty;
			$new['name'] = '--';
			foreach ($counts as $count)
				$new[$count['Item']['l7prot']] = $count[0]['num'];
			$data[] = $new;
		}
		
		$this->disableCache();
		$this->set('data', $data);
		$this->set('_serialize', 'data');
	}

	public function overview($name=null) {
		if ($this->dataset_id == 0)
			die();
		
		if ($name==null) {
			$this->Session->setFlash(__('Elaboration in progress... please wait'));
			$this->layout = 'tab';
			return;
		}
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$data = array();
		$hstat = $this->Item->find('all', array(
			'fields' => array('COUNT(Item.id) as num', 'SUM(Item.bsent) as datas', 'SUM(Item.brecv) as datar', 'SUM("Item"."brecv"+"Item"."bsent") as data', 'SUM(Item.duration) as dur', 'Item.cdate', 'Item.hour'),
			'group' => array('Item.cdate', 'Item.hour'),
			'conditions' => $conditions
		));
		for ($i=0; $i!=24; $i++) {
			$add['hour'] = $i;
			$add['days'] = array();
			$data[] = $add;
		}
		foreach ($hstat as $stat) {
			$i = $stat['Item']['hour'];
			$stat[0]['day'] = $stat['Item']['cdate'];
			$data[$i]['days'][] = $stat[0];
		}
		$this->disableCache();
		$this->set('data', $data);
		$this->set('_serialize', 'data');
	}

	public function hour_data($day=null, $hour=null, $size=null) {
		if ($day==null or $hour==null or $size=null)
			die();
		$this->layout = 'none';
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$ip = array();
		$data = array();
		$prots = array();
		$ports = array();
		$prot_cntrs = array();
		if (!isset($conditions['AND'])) {
			$conditions['AND'] = array();
		}
		$conditions['AND'][] = array('Item.cdate' => $day);

		// per minute
		$conds = $conditions;
		$conds['AND'][] = array('Item.hour' => $hour);
		$ips = $this->Item->find('all', array(
			'fields' => array('Item.min5', 'COUNT(DISTINCT Item.ips_id) as cnt'),
			'group' => array('Item.min5'),
			'order' => array('Item.min5 ASC'),
			'conditions' => $conds
		));
		$ipd = $this->Item->find('all', array(
			'fields' => array('Item.min5', 'COUNT(DISTINCT Item.ipd_id) as cnt'),
			'group' => array('Item.min5'),
			'order' => array('Item.min5 ASC'),
			'conditions' => $conds
		));
		$i = 0;
		$j = 0;
		foreach ($ips as $ipe) {
			for (; $i!=$ipe['Item']['min5']; $i+=5) {
				$add = array();
				$add['min'] = $i;
				$add['a'] = 0;
				$add['b'] = 0;
				$ip[] = $add;
			}
			$add['min'] = $ipe['Item']['min5'];
			
			$add['a'] = $ipe[0]['cnt'];
			$add['b'] = $ipd[$j][0]['cnt'];
			$ip[] = $add;
			$i = $ipe['Item']['min5'] + 5;
			$j++;
		}
		for (; $i!=60; $i+=5) {
			$add = array();
			$add['min'] = $i;
			$add['a'] = 0;
			$add['b'] = 0;
			$ip[] = $add;
		}

		$datad = $this->Item->find('all', array(
			'fields' => array('Item.min5', 'SUM(Item.bsent) as bs', 'SUM(Item.brecv) as br', 'COUNT(Item.id) as cnt', 'SUM(Item.duration) as dur'),
			'order' => array('Item.min5 ASC'),
			'group' => array('Item.min5'),
			'conditions' => $conds
		));
		$i = 0;
		$j = 0;
		foreach ($datad as $dd) {
			for (; $i!=$dd['Item']['min5']; $i+=5) {
				$add = array();
				$add['min'] = $i;
				$add['a'] = 0;
				$add['b'] = 0;
				$data[] = $add;
			}
			$add['min'] = $dd['Item']['min5'];
			
			$add['a'] = $dd[0]['bs'];
			$add['b'] = $dd[0]['br'];
			$data[] = $add;
			$i = $dd['Item']['min5'] + 5;
			$j++;
		}
		for (; $i!=60; $i+=5) {
			$add = array();
			$add['min'] = $i;
			$add['a'] = 0;
			$add['b'] = 0;
			$data[] = $add;
		}
		
		$this->set('data', json_encode($data));
		$this->set('ipcnt', json_encode($ip));
		
		// protocols
		$conds = $conditions;
		$conds['AND'][] = array('Item.hour' => $hour);
		$l7s = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as cnt', 'SUM("Item"."brecv"+"Item"."bsent") as data', 'SUM(Item.duration) as dur', 'Item.l7prot'),
				'group' => array('Item.l7prot'),
				'conditions' => $conds
		));
		foreach ($l7s as $l7) {
			$add = array();
			$add['l7'] = $l7['Item']['l7prot'];
			$add['cnt'] = $l7[0]['cnt'];
			$add['tot'] = $l7[0]['data'];
			$add['dur'] = $l7[0]['dur'];
			$prots[] = $add;
		}
		$this->set('prots', json_encode($prots));
		
		// destination ports map
		$portds = $this->Item->find('all', array(
			'fields' => array('COUNT(DISTINCT Item.ips_id) as cnt', 'COUNT(Item.id) as icnt', 'SUM("Item"."brecv"+"Item"."bsent") as data', 'SUM(Item.duration) as dur', 'Item.port_grp'),
			'group' => array('Item.port_grp'),
			'conditions' => $conds
		));
		$j = 0;
		$last_range = false;
		foreach ($portds as $dport) {
			$limit = $dport['Item']['port_grp'];
			if ($limit >= 65536) {
				$limit = 65536;
				$j = $limit - 1024;
				$last_range = true;
			}
			else if ($limit != 1024)
				$j = $limit - 2048;
			else
				$j = 0;
			$add = array();
			$add['ports'] = $j;
			$add['porte'] = $limit;
			$j = $limit;
			$add['num'] = $dport[0]['cnt'];
			$add['cnt'] = $dport[0]['icnt'];
			$add['tot'] = $dport[0]['data'];
			$add['dur'] = $dport[0]['dur'];
			$ports[] = $add;
		}
		if (!$last_range) {
			$add['ports'] = 65536 - 1024;
			$add['porte'] = 65536;
			$add['num'] = 0;
			$add['cnt'] = 0;
			$add['tot'] = 0;
			$add['dur'] = 0;
			$ports[] = $add;
		}
		$this->set('ports', json_encode($ports));
			
		// protocol country
		$prot_cntr = $this->Item->find('all', array(
			'fields' => array('COUNT(Item.id) as cnt', 'SUM(Item.brecv) as datar', 'SUM(Item.bsent) as datas', 'Item.country', 'Item.l7prot'),
			'group' => array('Item.country', 'Item.l7prot'),
			'conditions' => $conds
		));
		$nodes_p = array();
		$nodes = array();
		$links_flow = array();
		$links_data = array();
		foreach ($prot_cntr as $prt) {
			$l7 = $prt['Item']['l7prot'];
			$cntr = $prt['Item']['country'];
			$nodes_p[$l7] = array('name' => $l7);
			$nodes_p[$cntr] = array('name' => $cntr);
			$links_flow[] = array('source' => $l7, 'target' => $cntr, 'value' => $prt[0]['cnt']);
			if ($prt[0]['datas'])
				$links_data[] = array('source' => $l7, 'target' => $cntr, 'value' => $prt[0]['datas'], 'type' => 'sent');
			if ($prt[0]['datar'])
				$links_data[] = array('source' => $l7, 'target' => $cntr, 'value' => $prt[0]['datar'], 'type' => 'received');
		}
		$i = 0;
		foreach ($nodes_p as $key => $node) {
			$nodes[] = $node;
			$nodes_p[$key] = $i;
			$i++;
		}
		foreach ($links_flow as &$pv) {
			$pv['source'] = $nodes_p[$pv['source']];
			$pv['target'] = $nodes_p[$pv['target']];
		}
		foreach ($links_data as &$pv) {
			$pv['source'] = $nodes_p[$pv['source']];
			$pv['target'] = $nodes_p[$pv['target']];
		}
		$prot_cntrs['nodes'] = $nodes;
		$prot_cntrs['links'] = $links_flow;
		$prot_cntrs['data'] = $links_data;
		$this->set('prot_cntrs', json_encode($prot_cntrs));
	}

	public function geomap() {
		if ($this->dataset_id == 0)
			die();
		
		$this->layout = 'tab';
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		// country
		$cntr_raw = $this->Item->find('all', array(
			'fields' => array('COUNT(Item.id) as cnt', 'SUM(Item.brecv) as datar', 'SUM(Item.bsent) as datas', 'Item.country'),
			'group' => array('Item.country'),
			'conditions' => $conditions
		));
		$countries = array();
		$cntr_id = array();
		$i = 0;
		foreach ($cntr_raw as $cnt) {
			$cntr_id[$cnt['Item']['country']] = $i++;
			$add = array();
			$add['country'] = $cnt['Item']['country'];
			$add['data']= array('flows' => $cnt[0]['cnt'], 'sent' => $cnt[0]['datas'], 'received' => $cnt[0]['datar'], 'tot' => $cnt[0]['datas']+$cnt[0]['datar']);
			$countries[] = $add;
		}
		$this->set('countries', json_encode($countries));
		$this->set('countries_id', json_encode($cntr_id));
	}

	function mycharts() {
		if ($this->dataset_id == 0)
			die();
		$this->layout = 'tab';
	}

	function globalview($name=null) {
		if ($this->dataset_id == 0)
			die();
		$this->layout = 'tab';

		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		if ($name == null) {
			// destination ports map
			$portds = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as icnt', 'SUM("Item"."brecv"+"Item"."bsent") as data', 'SUM(Item.duration) as dur', 'Item.port_grp'),
				'group' => array('Item.port_grp'),
				'conditions' => $conditions
			));
			$last_range = false;
			$j = 0;
			foreach ($portds as $dport) {
				$limit = $dport['Item']['port_grp'];
				if ($limit >= 65536) {
					$limit = 65536;
					$j = $limit - 1024;
					$last_range = true;
				}
				else if ($limit != 1024) {
					$j = $limit - 2048;
				}
				else
					$j = 0;
				$add = array();
				$add['ports'] = $j;
				$add['porte'] = $limit;
				$add['cnt'] = $dport[0]['icnt'];
				$add['tot'] = $dport[0]['data'];
				$add['dur'] = $dport[0]['dur'];
				$ports[] = $add;
			}
			if (!$last_range) {
				$add['ports'] = 65536 - 1024;
				$add['porte'] = 65536;
				$add['num'] = 0;
				$add['cnt'] = 0;
				$add['tot'] = 0;
				$add['dur'] = 0;
				$ports[] = $add;
			}
			$this->set('ports', json_encode($ports));
			
			// hours 
			$hstat = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as flows', 'SUM(Item.bsent) as datas', 'SUM(Item.brecv) as datar', 'SUM(Item.duration) as dur', 'Item.hour'),
				'group' => array('Item.hour'),
				'conditions' => $conditions
			));
			$data = array();
			foreach ($hstat as $stat) {
				$new = array();
				$new['flows'] = $stat[0]['flows'];
				$new['datas'] = $stat[0]['datas'];
				$new['datar'] = $stat[0]['datar'];
				$new['dur'] = $stat[0]['dur'];
				$new['hour'] = $stat['Item']['hour'];
				$data[] = $new;
			}
			$this->set('hours', json_encode($data));
		}
		
		// protocol vs countries
		if ($name == 'countries') {
			$prot_cntr = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as cnt', 'SUM(Item.brecv) as datar', 'SUM(Item.bsent) as datas', 'Item.country', 'Item.l7prot'),
				'group' => array('Item.country', 'Item.l7prot'),
				'conditions' => $conditions
			));
			$nodes_p = array();
			$nodes = array();
			$links_flow = array();
			$links_data = array();
			foreach ($prot_cntr as $prt) {
				$l7 = $prt['Item']['l7prot'];
				$cntr = $prt['Item']['country'];
				$nodes_p[$l7] = array('name' => $l7);
				$nodes_p[$cntr] = array('name' => $cntr);
				$links_flow[] = array('source' => $l7, 'target' => $cntr, 'value' => $prt[0]['cnt']);
				if ($prt[0]['datas'])
					$links_data[] = array('source' => $l7, 'target' => $cntr, 'value' => $prt[0]['datas'], 'type' => 'sent');
				if ($prt[0]['datar'])
					$links_data[] = array('source' => $l7, 'target' => $cntr, 'value' => $prt[0]['datar'], 'type' => 'received');
			}
			$i = 0;
			foreach ($nodes_p as $key => $node) {
				$nodes[] = $node;
				$nodes_p[$key] = $i;
				$i++;
			}
			foreach ($links_flow as &$pv) {
				$pv['source'] = $nodes_p[$pv['source']];
				$pv['target'] = $nodes_p[$pv['target']];
			}
			foreach ($links_data as &$pv) {
				$pv['source'] = $nodes_p[$pv['source']];
				$pv['target'] = $nodes_p[$pv['target']];
			}
			$prot_cntrs['nodes'] = $nodes;
			$prot_cntrs['links'] = $links_flow;
			$prot_cntrs['data'] = $links_data;
			//$this->set('prot_cntrs', json_encode($prot_cntrs));
			$this->set('prot_cntrs', $prot_cntrs);
			$this->set('_serialize', 'prot_cntrs');
		}
		
		// days vs protocols
		if ($name == 'days') {
			$prot_day = $this->Item->find('all', array(
				'fields' => array('COUNT(Item.id) as cnt', 'SUM(Item.brecv) as datar', 'SUM(Item.bsent) as datas', 'Item.cdate', 'Item.l7prot'),
				'group' => array('Item.cdate', 'Item.l7prot'),
				'conditions' => $conditions
			));
			$nodes_p = array();
			$nodes = array();
			$links_flow = array();
			$links_data = array();
			foreach ($prot_day as $prt) {
				$l7 = $prt['Item']['l7prot'];
				$day = $prt['Item']['cdate'];
				$nodes_p[$l7] = array('name' => $l7);
				$nodes_p[$day] = array('name' => $day);
				$links_flow[] = array('source' => $l7, 'target' => $day, 'value' => $prt[0]['cnt']);
				if ($prt[0]['datas'])
					$links_data[] = array('source' => $l7, 'target' => $day, 'value' => $prt[0]['datas'], 'type' => 'sent');
				if ($prt[0]['datar'])
					$links_data[] = array('source' => $l7, 'target' => $day, 'value' => $prt[0]['datar'], 'type' => 'received');
			}
			$i = 0;
			foreach ($nodes_p as $key => $node) {
				$nodes[] = $node;
				$nodes_p[$key] = $i;
				$i++;
			}
			foreach ($links_flow as &$pv) {
				$pv['source'] = $nodes_p[$pv['source']];
				$pv['target'] = $nodes_p[$pv['target']];
			}
			foreach ($links_data as &$pv) {
				$pv['source'] = $nodes_p[$pv['source']];
				$pv['target'] = $nodes_p[$pv['target']];
			}
			$prot_days['nodes'] = $nodes;
			$prot_days['links'] = $links_flow;
			$prot_days['data'] = $links_data;
			//$this->set('prot_days', json_encode($prot_days));
			$this->set('prot_days', $prot_days);
			$this->set('_serialize', 'prot_days');
		}
	}

	function ipsource() {
		if ($this->dataset_id == 0)
			die();
		$this->layout = 'none';
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		
		$this->paginate = array('Item' => array(
			'fields' => array('Item.ips_id', 'Item.ips', 'Item.fcnt', 'Item.bout', 'Item.bin'),
			'order' => array('Item.fcnt' => 'DESC'),
			'group' => array('Item.ips_id'),
			'conditions' => $conditions,
			'limit' => $this->limit
		));
		
		$this->set('items', $this->paginate('Item'));
	}

	function ipdestin() {
		if ($this->dataset_id == 0)
			die();
		$this->layout = 'none';
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		
		$this->paginate = array('Item' => array(
			'fields' => array('Item.ipd_id', 'Item.ipd', 'Item.fcnt', 'Item.bout', 'Item.bin'),
			'order' => array('Item.fcnt' => 'DESC'),
			'group' => array('Item.ipd_id'),
			'conditions' => $conditions,
			'limit' => $this->limit
		));
		
		$this->set('items', $this->paginate('Item'));
	}

	function timeline($ip_id = null) {
		if ($this->dataset_id == 0)
			die();
		$conditions = $this->Conditions();
		if ($ip_id == null) {
			$this->layout = 'tab';
			$full = true;
		}
		else {
			$this->layout = 'none';
			$full = false;
			if (!isset($conditions['AND'])) {
				$conditions['AND'] = array();
			}
			$conditions['AND'][] = array('OR' => array('Item.ips_id' => $ip_id, 'Item.ipd_id' => $ip_id));
		}
		
		$this->Item->recursive = -1;
		$first_d_con = $this->Item->find('first', array(
				'fields' => array('Item.cdate', 'Item.ctime', 'Item.hour', 'Item.min5'),
				'conditions' => $conditions,
				'order' => array('Item.cdate' => 'ASC', 'Item.ctime' => 'ASC')
		));
		$last_d_con = $this->Item->find('first', array(
				'fields' => array('Item.cdate', 'Item.ctime'),
				'conditions' => $conditions,
				'order' => array('Item.cdate' => 'DESC', 'Item.ctime' => 'DESC')
		));
		$tfirst = $first_d_con['Item']['cdate'].' '.$first_d_con['Item']['ctime'];
		$ts = strtotime($tfirst);
		$te = strtotime($last_d_con['Item']['cdate'].' '.$last_d_con['Item']['ctime']);
		$tbase = ($te - $ts)/50/60;
		$cnt = 0;
		if ($tbase < 60) {
			$ts = strtotime($first_d_con['Item']['cdate'].' '.$first_d_con['Item']['hour'].':'.$first_d_con['Item']['min5'].':00');
			$group = array('Item.cdate', 'Item.hour', 'Item.min5');
			$fields = array('Item.cdate', 'Item.hour', 'Item.min5', 'Item.fcnt', 'Item.bout', 'Item.bin', 'SUM(Item.duration) as dur');
			$order = array('Item.cdate' => 'ASC', 'Item.hour' => 'ASC', 'Item.min5' => 'ASC');
			$tbase = ($te - $ts);
			$cnt = round($tbase/300, 0);
			$base = 300;
		}
		else if ($tbase < 1440) {
			$ts = strtotime($first_d_con['Item']['cdate'].' '.$first_d_con['Item']['hour'].':00:00');
			$group = array('Item.cdate', 'Item.hour');
			$fields = array('Item.cdate', 'Item.hour', 'Item.fcnt', 'Item.bout', 'Item.bin', 'SUM(Item.duration) as dur');
			$order = array('Item.cdate' => 'ASC', 'Item.hour' => 'ASC');
			$tbase = ($te - $ts);
			$cnt = round($tbase/3600, 0);
			$base = 3600;
		}
		else if ($tbase < 10080) { // day
			$ts = strtotime($first_d_con['Item']['cdate'].' 00:00:00');
			$group = array('Item.cdate');
			$fields = array('Item.cdate', 'Item.fcnt', 'Item.bout', 'Item.bin', 'SUM(Item.duration) as dur');
			$order = array('Item.cdate' => 'ASC');
			$tbase = ($te - $ts);
			$cnt = round($tbase/86400, 0);
			$base = 86400;
		}
		else if ($tbase < 40320)  { // week 
			$ts = strtotime($first_d_con['Item']['cdate'].' 00:00:00');
			$group = array('Item.year', 'Item.week');
			$fields = array('Item.year', 'Item.week', 'Item.fcnt', 'Item.bout', 'Item.bin', 'SUM(Item.duration) as dur');
			$order = array('Item.year' => 'ASC', 'Item.week' => 'ASC');
			$tbase = ($te - $ts);
			$cnt = round($tbase/604800, 0);
			$base = 604800;
		}
		else { // month 
			$ts = strtotime($first_d_con['Item']['cdate'].' 00:00:00');
			$group = array('Item.year', 'Item.month');
			$fields = array('Item.year', 'Item.month', 'Item.fcnt', 'Item.bout', 'Item.bin', 'SUM(Item.duration) as dur');
			$order = array('Item.year' => 'ASC', 'Item.month' => 'ASC');
			$tbase = ($te - $ts);
			$cnt = round($tbase/2419200, 0);
			$base = 2419200;
		}
		
		$elements = $this->Item->find('all', array(
			'fields' => $fields,
			'conditions' => $conditions,
			'order' => $order,
			'group' => $group,
		));

		$timeline = array('f' => array(), 'i' => array(), 'o' => array(), 't' => array(), 'n' => $cnt+1);
		$tlast = $tfirst;
		foreach ($elements as $elem) {
			switch ($base) {
			case 300:
				$tem = $elem['Item']['cdate'].' '.$elem['Item']['hour'].':'.$elem['Item']['min5'];
				$te = strtotime($tem.':59');
				break;
			case 3600:
				$tem = $elem['Item']['cdate'].' '.$elem['Item']['hour'];
				$te = strtotime($tem.':59:59');
				break;
			case 86400:
				$te = strtotime($elem['Item']['cdate'].' 23:59:59');
				$tem = $elem['Item']['cdate'];
				break;
			case 604800:
				$tem = $elem['Item']['year'].' ['.$elem['Item']['week'].']w';
				$te = strtotime($elem['Item']['year'].'W'.$elem['Item']['week'])+$base;
				break;
			default:
				$tem = $elem['Item']['year'].' ['.$elem['Item']['month'].']m';
				if ($elem['Item']['month'] < 10)
					$te = strtotime($elem['Item']['year'].'-0'.$elem['Item']['month'].'-01')+$base;
				else
					$te = strtotime($elem['Item']['year'].'-'.$elem['Item']['month'].'-01')+$base;
				break;
			}
			$x = round(($te - $ts)/$base, 0);
			$timeline['f'][] = array('x' => $x, 'y' => $elem['Item']['fcnt'], 'tm' => $tem);
			$timeline['i'][] = array('x' => $x, 'y' => $elem['Item']['bin'], 'tm' => $tem);
			$timeline['o'][] = array('x' => $x, 'y' => $elem['Item']['bout'], 'tm' => $tem);
			$timeline['t'][] = array('x' => $x, 'y' => $elem['Item']['bout'] + $elem['Item']['bin'], 'tm' => $tem);
		}
		
		$this->set('timeline', json_encode($timeline));
		$this->set('full', $full);
	}

	function protocols() {
		if ($this->dataset_id == 0)
			die();
		$this->layout = 'tab';
		
		$this->Item->recursive = -1;
		$conditions = $this->Conditions();
		$group = array('Item.l7prot');
		$fields = array('Item.l7prot', 'COUNT(DISTINCT(Item.ips_id)) as ips', 'COUNT(DISTINCT(Item.ipd_id)) as ipd','Item.fcnt', 'Item.bout', 'Item.bin', 'SUM(Item.duration) as dur');
		
		$elements = $this->Item->find('all', array(
			'fields' => $fields,
			'conditions' => $conditions,
			'group' => $group,
		));
		
		$protos = array();
		foreach ($elements as $elem) {
			$protos[] = array('p' => $elem['Item']['l7prot'], 'ips' => $elem[0]['ips'], 'ipd' => $elem[0]['ipd'],'f' => $elem['Item']['fcnt'],'i' => $elem['Item']['bin'], 'o' => $elem['Item']['bout'], 'd' => $elem[0]['dur']);
		}
		$this->set('protocols', json_encode($protos));
	}
}
