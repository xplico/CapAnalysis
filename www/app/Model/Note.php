<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppModel', 'Model');
/**
 * Note Model
 *
 * @property User $User
 * @property Item $Item
 */
class Note extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'body';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'body' => array(
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'url' => array(
				'rule' => array('url'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Dataset' => array(
			'className' => 'Dataset',
			'foreignKey' => 'dataset_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Capfile' => array(
			'className' => 'Capfile',
			'foreignKey' => 'capfile_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
