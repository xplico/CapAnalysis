<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppController', 'Controller');

class CapfilesController extends AppController {
    public $components = array('Upload', 'Session', 'RequestHandler', 'String');
    public $uses = array('Capfile', 'Dataset');
    var $paginate = array('limit' => 7);
    public $helpers = array('String');
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
    
    public function beforeFilter() {
        parent::beforeFilter();
        if ($this->Session->check('group_id')) {
            $this->group_id = $this->Session->read('group_id');
        }
        else {
            $this->redirect(array('controller' => 'users', 'action' => 'login'));
            die();
        }
        if ($this->Session->check('dataset_id')) {
            $this->dataset_id = $this->Session->read('dataset_id');
            $this->Capfile->Item->setSource('items_'.$this->dataset_id);
        }
    }
    
    public function index($id = null) {
        if ($id != null) {
			$this->Capfile->Dataset->recursive = -1;
            $this->Capfile->Dataset->id = $id;
            if (!$this->Capfile->Dataset->exists()) {
                $this->redirect(array('controller' => 'users', 'action' => 'login'));
                return;
            }
            if ($this->Session->check('demo')) {
                $params['conditions'] = array('Dataset.group_id' => $this->group_id, 'Dataset.name' => 'Set '.$this->Session->read('ip_usr'), 'Dataset.id' => $id);
                $ds_count = $this->Capfile->Dataset->find('count', $params);
                if (!$ds_count) {
                    $this->Session->setFlash(__('Oops...'));
                    $this->redirect(array('controller' => 'users', 'action' => 'login'));
                    die();
                }
            }
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
            
            $root_ds = Configure::read('Dataset.root').'/ds_'.$id;
            $this->dataset_id = $id;
            $this->Session->write('dataset_id', $id);
            $this->Session->write('dspath', $root_ds);
            $this->Capfile->Item->setSource('items_'.$id);
        }
        $upload_max_filesize = $this->String->toByteSize(ini_get('upload_max_filesize'));
        //$memory_limit = ini_get('memory_limit');
        $memory_limit = $upload_max_filesize;
        $post_max_size = $this->String->toByteSize(ini_get('post_max_size'));
        $min = $post_max_size;
        if ($min > $memory_limit)
            $min = $memory_limit;
        if ($min > $upload_max_filesize)
            $min = $upload_max_filesize;
        $limit_on = 0;
        if ($this->Session->check('demo')) {
            $params = array();
            $params['fields'] = array('SUM(Capfile.data_size) as size');
            $params['conditions'] = array('Dataset.group_id' => $this->group_id, 'Dataset.name' => 'Set '.$this->Session->read('ip_usr'));
            $file_lim_size = $this->Dataset->Capfile->find('first', $params);
            $file_lim_size = $file_lim_size[0]['size']+0;
            if ($file_lim_size >= $this->Session->read('demo_limit'))
                $limit_on = 1;
        }
        $this->Capfile->recursive = -1;
        $this->paginate['conditions'] = array('Capfile.dataset_id' => $this->dataset_id);
        $this->paginate['order'] = 'Capfile.id DESC';
        $file_count = $this->Capfile->find('count', array('conditions' => array('Capfile.dataset_id' => $this->dataset_id)));
        $this->set('tot_files', $file_count);
        $this->set('capfiles', $this->paginate());
        $this->set('max_size', $this->String->size($min));
        $this->set('limit_on', $limit_on);
    }
    
    public function datatip($id) {
        $this->layout = 'none';
        
        /* protocol usage */
        $this->Capfile->Item->recursive = -1;
        $l7port = $this->Capfile->Item->find('all', array(
            'fields' => array('COUNT(Item.id) as icnt', 'Item.l7prot'),
            'order' => array('icnt DESC'),
            'group' => array('Item.l7prot'),
            'conditions' => array('Item.capfile_id' => $id),
            'limit' => 5
        ));
        $ptot = $this->Capfile->Item->find('count', array('conditions' => array('Item.capfile_id' => $id)));
        $prots = array('num' => '', 'labels' => '', 'empty' => true);
        foreach ($l7port as $l7) {
            $ptot -= $l7[0]['icnt'];
            if ($prots['num'] != '') {
                $prots['num'] .= ','.$l7[0]['icnt'];
                $prots['labels'] .= ', "%%.% - '.$l7['Item']['l7prot'].'"';
            }
            else {
                $prots['empty'] = false;
                $prots['num'] = $l7[0]['icnt'];
                $prots['labels'] = '"%%.% - '.$l7['Item']['l7prot'].'"';
            }
        }
        $prots['num'] .= ','.$ptot;
        $prots['labels'] .= ', "%%.% - Other"';
        $this->set('l7prot', $prots);
        
        /* In/Out traffic */
        $iotraf = $this->Capfile->Item->find('all', array(
            'fields' => array('SUM(Item.bsent) as vout', 'SUM(Item.brecv) as vin'),
            'group' => array('Item.capfile_id'),
            'conditions' => array('Item.capfile_id' => $id),
        ));
        $this->set('iotraf', $iotraf);
        $this->set('flid', $id);
        $this->disableCache();
    }
    
    public function add() {
        $data = null;
        if ($this->request->is('post')) {
            $data = $this->Upload->post();
        }
        else if ($this->request->is('get') or $this->request->is('head')) {
            $data = $this->Upload->get();
        }
        $this->disableCache();
        $this->set('jsond', $data);
        $this->set('_serialize', 'jsond');
    }
    
    public function delete($id = null) {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        if ($this->Session->check('demo')) {
            $this->redirect(array('controller' => 'users', 'action' => 'login'));
            die();
        }
        $this->Capfile->id = $id;
        if (!$this->Capfile->exists()) {
            throw new NotFoundException(__('Invalid capfile'));
        }
        if ($this->Capfile->delete()) {
            $this->Session->setFlash(__('Capfile deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('Capfile was not deleted'));
        $this->redirect(array('action' => 'index'));
    }

    function inputdir() {
        if ($this->dataset_id == 0)
            die();
        $this->layout = 'tab';
    }

    function pcapoverip() {
        if ($this->dataset_id == 0)
            die();
        
        $this->layout = 'tab';
        
        $this->Dataset->recursive = -1;
        $this->Dataset->id = $this->dataset_id;
        $ds = $this->Dataset->read();
        $fport = $this->Session->read('dspath').'/tmp/pcap_ip.port';
        if (file_exists($fport)) {
            $lines = file($fport);
            $this->set('dataset_name', $ds['Dataset']['name']);
            $this->set('ip', $_SERVER['SERVER_ADDR']);
            $this->set('port', $lines[0]);
            $this->set('capana', true);
        }
        else {
            $this->set('capana', false);
        }
    }
}
