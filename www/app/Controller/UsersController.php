<?php
  /*
   CapAnalysis

   Copyright 2012-2016 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {
	public $components = array('Session');
	
	public function login() {
        touch(Configure::read('Dataset.root').'/tmp/registred');
        //$this->Session->destroy();
		$this->Session->write('vers', '1.2.3');
        $this->Session->write('ip_usr', str_replace(array('.', ':'), '', $_SERVER['REMOTE_ADDR']));
        $this->Session->write('demo_limit', 20971520); // 20M
        //$this->Session->write('demo', true);
        
		/* basic version */
		if (!$this->Session->check('group_id')) {
            $this->layout = 'none';
            $this->Session->write('group_id', 2);
            if (!$this->Session->check('demo'))
                $this->redirect(array('controller' => 'datasets', 'action' => 'index'));
        }
        else {
            $this->redirect(array('controller' => 'datasets', 'action' => 'index'));
        }
	}

    public function logout() {
        $this->Session->destroy();
        $this->redirect(array('controller' => 'users', 'action' => 'login'));
    }
}
