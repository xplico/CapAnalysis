<?php
// titolo
$title_page = 'CapManual';

// top menu (solo una voce a sinistra)
$header_left = array(
					 array('help' => 'CapAnalysis Manual', 'link' => $ROOT_APP.'manual/intro', 'title' => 'Manual'),
					 array('help' => 'Support', 'link' => 'mailto:support@capanalysis.net', 'title' => 'Support'),
				);
$header_left_active = 0;

$header_right = array(
					 array('help' => '', 'link' => $ROOT_APP.'default/credits', 'title' => 'Credits'),
				);
$header_right_active = -1;
