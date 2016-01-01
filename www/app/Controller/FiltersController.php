<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppController', 'Controller');
/**
 * Filters Controller
 *
 * @property Filter $Filter
 */
class FiltersController extends AppController {

	public function index() {
		$this->Filter->recursive = 0;
		$this->set('filters', $this->paginate());
	}

	public function view($id = null) {
		$this->Filter->id = $id;
		if (!$this->Filter->exists()) {
			throw new NotFoundException(__('Invalid filter'));
		}
		$this->set('filter', $this->Filter->read(null, $id));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Filter->create();
			if ($this->Filter->save($this->request->data)) {
				$this->Session->setFlash(__('The filter has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The filter could not be saved. Please, try again.'));
			}
		}
	}

	public function edit($id = null) {
		$this->Filter->id = $id;
		if (!$this->Filter->exists()) {
			throw new NotFoundException(__('Invalid filter'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Filter->save($this->request->data)) {
				$this->Session->setFlash(__('The filter has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The filter could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Filter->read(null, $id);
		}
	}
	
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Filter->id = $id;
		if (!$this->Filter->exists()) {
			throw new NotFoundException(__('Invalid filter'));
		}
		if ($this->Filter->delete()) {
			$this->Session->setFlash(__('Filter deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Filter was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
