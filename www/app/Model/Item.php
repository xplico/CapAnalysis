<?php
  /*
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
  */

App::uses('AppModel', 'Model');
/**
 * Item Model
 *
 * @property Dataset $Dataset
 * @property Capfile $Capfile
 * @property Classification $Classification
 * @property Note $Note
 */
class Item extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'l7prot';

	public $virtualFields = array(
		'fcnt' => 'COUNT(Item.id)',
		'bout' => 'SUM(Item.bsent)',
		'bin' => 'SUM(Item.brecv)',
		'ipd' => 'FIRST(Item.ip_dst)',
		'ips' => 'FIRST(Item.ip_src)'
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
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
		),
		'Classification' => array(
			'className' => 'Classification',
			'foreignKey' => 'classification_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
	);

}
