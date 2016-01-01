<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppController', 'Controller');
/**
 * Notes Controller
 *
 * @property Note $Note
 */
class NotesController extends AppController {

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Note->recursive = 0;
		$this->set('notes', $this->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Note->id = $id;
		if (!$this->Note->exists()) {
			throw new NotFoundException(__('Invalid note'));
		}
		$this->set('note', $this->Note->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Note->create();
			if ($this->Note->save($this->request->data)) {
				$this->Session->setFlash(__('The note has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note could not be saved. Please, try again.'));
			}
		}
		$users = $this->Note->User->find('list');
		$items = $this->Note->Item->find('list');
		$this->set(compact('users', 'items'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Note->id = $id;
		if (!$this->Note->exists()) {
			throw new NotFoundException(__('Invalid note'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Note->save($this->request->data)) {
				$this->Session->setFlash(__('The note has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Note->read(null, $id);
		}
		$users = $this->Note->User->find('list');
		$items = $this->Note->Item->find('list');
		$this->set(compact('users', 'items'));
	}

/**
 * delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Note->id = $id;
		if (!$this->Note->exists()) {
			throw new NotFoundException(__('Invalid note'));
		}
		if ($this->Note->delete()) {
			$this->Session->setFlash(__('Note deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Note was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
