<?php
// menu di sinistra (default nessun menu aperto -false-)
$menuleft = array('active' => '-1',
                  'sections' => array(
                      array('name' => 'Manuale', 'sub' => array(
                                array('name' => 'Utilizzo', 'link' => $ROOT_APP.'mvc/intro', 'help' => ''),
                                array('name' => 'Template', 'link' => $ROOT_APP.'mvc/template', 'help' => ''),
                                array('name' => 'Controller', 'link' => $ROOT_APP.'mvc/control', 'help' => ''),
                                array('name' => 'Page', 'link' => $ROOT_APP.'mvc/page', 'help' => ''),
                                array('name' => 'Top Menu', 'link' => $ROOT_APP.'menu/top', 'help' => ''),
                                array('name' => 'Lateral Menu', 'link' => $ROOT_APP.'menu/lat', 'help' => ''),
                                ))
                      ));
