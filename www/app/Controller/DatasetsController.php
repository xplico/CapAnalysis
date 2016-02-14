<?php
  /*
   CapAnalysis

   Copyright 2012-2016 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppController', 'Controller');
/**
 * Datasets Controller
 *
 * @property Dataset $Dataset
 */
class DatasetsController extends AppController {
  var $paginate = array('limit' => 7);
  var $group_id = 2;
  public $components = array('String', 'Session', 'Capana');
    public $helpers = array('String');

    private function add_demo() {
        $this->Dataset->recursive = -1;
        $this->Dataset->create();
        $data = array();
        $data['Dataset']['name'] = 'Set '.$this->Session->read('ip_usr');
        $data['Dataset']['share'] = md5($data['Dataset']['name'].time());
        $data['Dataset']['group_id'] = $this->group_id;
        $data['Dataset']['depth'] = 'EOL:'.date("Y-m-d", time()+24*3600);
        //print_r($data);die();
        if ($this->Dataset->save($data)) {
            mkdir(Configure::read('Dataset.root').'/ds_'.$this->Dataset->id);
            $this->Session->setFlash(__('The Dataset has been saved'));
            return true;
        }
        $this->Session->setFlash(__("Merda! It's a bug"));
        return false;
    }
    
  public function beforeFilter() {
    parent::beforeFilter();
    /* check user */
    if ($this->Session->check('group_id')) {
      $this->group_id = $this->Session->read('group_id');
    }
    else {
      $this->redirect(array('controller' => 'users', 'action' => 'login'));
      die();
    }

    if ($this->Capana->check() == False) {
      $this->redirect(array('controller' => 'capinstall'));
      die();
    }
  }
    
  public function index() {
    $this->Dataset->recursive = -1;
    $this->Dataset->Capfile->recursive = -1;
        $params = array();
        
        if ($this->Session->check('demo')) {
            $params['conditions'] = array('Dataset.group_id' => $this->group_id, 'Dataset.name' => 'Set '.$this->Session->read('ip_usr'));
            $ds_count = $this->Dataset->find('count', $params);
            if (!$ds_count) {
                $this->add_demo();
                $this->set('demo_info', true);
            }
            $params = array();
        }
    $ds_count = $this->Dataset->find('count', $params);
    $file_count = $this->Dataset->Capfile->find('count', $params);
        $params['fields'] = array('SUM(Capfile.data_size) as size');
    $file_size = $this->Dataset->Capfile->find('first', $params);
    $file_size = $file_size[0]['size']+0;
    $file_size = $this->String->size($file_size);
    $this->Session->delete('dataset_id');
    $this->set('ds_count', $ds_count);
    $this->set('file_count', $file_count);
    $this->set('file_size', $file_size);
  }

  public function dataset() {
    $this->layout = 'none';
    
    $this->paginate = array('Dataset' => array(
      'fields' => array('COUNT(Capfile.id) as fcnt', 'SUM(Capfile.data_size) as fsize', 'Dataset.name', 'Dataset.id', 'Dataset.depth'),
      'joins' => array(array('type' => 'LEFT', 'alias' => 'Capfile', 'table' => 'capfiles', 'conditions' => array('Dataset.id = Capfile.dataset_id'))),
      'group' => array('Dataset.name', 'Dataset.id'),
      'order' => 'Dataset.id DESC',
      'conditions' => array('Dataset.group_id' => $this->group_id),
      'limit' => $this->paginate['limit']
    ));
        if ($this->Session->check('demo')) {
            $this->paginate['Dataset']['conditions']['Dataset.name'] = 'Set '.$this->Session->read('ip_usr');
        }

    $this->Dataset->recursive = -1;
    $this->set('datasets', $this->paginate('Dataset'));
    $this->disableCache();
  }
  
  public function datatip($id) {
    $this->layout = 'none';
    
    /* protocol usage */
    $this->Dataset->Item->setSource('items_'.$id);
    $this->Dataset->Item->recursive = -1;
    $l7port = $this->Dataset->Item->find('all', array(
      'fields' => array('COUNT(Item.id) as icnt', 'Item.l7prot'),
      'order' => array('icnt DESC'),
      'group' => array('Item.l7prot'),
      'limit' => 5
    ));
    $ptot = $this->Dataset->Item->find('count', array('conditions' => array('Item.dataset_id' => $id)));
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
    $iotraf = $this->Dataset->Item->find('all', array(
      'fields' => array('SUM(Item.bsent) as vout', 'SUM(Item.brecv) as vin'),
      'group' => array('Item.dataset_id'),
      'conditions' => array('Item.dataset_id' => $id),
    ));
    $this->set('iotraf', $iotraf);
    $this->set('dsid', $id);
    $this->disableCache();
  }
    
  public function sharetip($id = null) {
    $this->layout = 'none';
        
    if ($id != null) {
        $this->Dataset->id = $id;
        //$p = $this->Dataset->read(null, $id);
        $url = Router::url(array('controller'=>'dataset', 'action'=>'share'), true);
    }
    else {
        $url = __('Unknow');
    }
    $this->set('url', $url);
    $this->disableCache();
  }


  public function add() {
    $this->layout = 'tab';
    $this->Dataset->recursive = -1;
    $added = false;
        $error = true;
    if ($this->request->is('post') && !$this->Session->check('demo')) {
      $this->Dataset->create();
      $this->request->data['Dataset']['share'] = md5($this->request->data['Dataset']['name'].time());
      $this->request->data['Dataset']['group_id'] = $this->group_id;
      //print_r($this->request->data['Dataset']);die();
      switch ($this->request->data['Dataset']['depth']) {
      case '0':
          $this->request->data['Dataset']['depth'] = '';
          $error = false;
          break;
          
      case '1': // End of Life
          $this->request->data['Dataset']['depth'] = 'EOL:'.$this->request->data['Dataset']['eol'];
          $error = false;
          break;
          
      case '2': // time range
          if (is_numeric($this->request->data['Dataset']['td'])) {
              $this->request->data['Dataset']['depth'] = 'TD:'.$this->request->data['Dataset']['td'];
              $error = false;
          }
          break;
          
      case '3': // flows number
          if (is_numeric($this->request->data['Dataset']['fd'])) {
              $this->request->data['Dataset']['depth'] = 'FD:'.$this->request->data['Dataset']['fd'];
              $error = false;
          }
          break;
          
      case '4': // pcap size limit
          if (is_numeric($this->request->data['Dataset']['sz'])) {
              $this->request->data['Dataset']['depth'] = 'SZ:'.$this->request->data['Dataset']['sz'];
              $error = false;
          }
          break;
      }
      if ($error == true) {
        $this->Session->setFlash(__('Depth value is wrong. Please, try again.'));
            }
            else if ($this->Dataset->save($this->request->data)) {
        mkdir(Configure::read('Dataset.root').'/ds_'.$this->Dataset->id);
        $this->Session->setFlash(__('The Dataset has been saved'));
        $this->request->data['Dataset']['name'] = '';
        $added = true;
      }
      else {
        $this->Session->setFlash(__('The dataset could not be saved. Please, try again.'));
      }
    }
    $ds_count = $this->Dataset->find('count');
    $this->set('ds_count', $ds_count);
    $this->set('added', $added);
    $this->disableCache();
  }
        
  public function delete($id = null) {
    if ($this->Session->check('demo')) {
      $this->redirect(array('controller' => 'users', 'action' => 'login'));
      die();
    }
    if (!$this->request->is('post')) {
      throw new MethodNotAllowedException();
    }
    $this->Dataset->id = $id;
    if (!$this->Dataset->exists()) {
      throw new NotFoundException(__('Invalid dataset'));
    }
    /* exist at least one session */
    $del_ds_file = Configure::read('Dataset.root').'/ds_'.$id.'/delete';
    touch($del_ds_file);
    // wait records db cancellation
    do {
      sleep(1);
    } while (file_exists($del_ds_file));
    $this->Session->setFlash(__('Dataset deleted'));
    $this->redirect(array('action' => 'index'));
  }

  public function rules() {
    $this->layout = 'tab';
  }
}
