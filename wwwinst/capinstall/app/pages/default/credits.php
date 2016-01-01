<?php
$credits = array();
$credits[] = array('en' => 1, 'title' => 'Essential', 'desc' => '<a href="http://www.capanalysis.net" title="Essemtial">Essential</a> is a minimal PHP framework to easly develop Web User Interface. You may use Essential under the terms of the MIT License.');
$credits[] = array('en' => 1, 'title' => 'CakePHP', 'desc' => '<a href="http://cakephp.org/" title="CakePHP">CakePHP</a> makes building web applications simpler, faster and require less code. CakePHP is licensed under the MIT license which makes it perfect for use in commercial applications..');
$credits[] = array('en' => 1, 'title' => 'Bootstrap', 'desc' => '<a href="http://getbootstrap.com/" title="Bootstrap">Bootstrap</a>: Sleek, intuitive, and powerful front-end framework for faster and easier web development. Licensed under the Apache License, Version 2.0.');
$credits[] = array('en' => 1, 'title' => 'jQuery', 'desc' => '<a href="http://jquery.com/" title="jQuery">jQuery</a> is a fast and concise JavaScript Library that simplifies HTML document traversing, event handling, animating, and Ajax interactions for rapid web development. jQuery is released under the terms of the MIT license.');
$credits[] = array('en' => 0, 'title' => 'SQLite', 'desc' => '<a href="http://www.sqlite.org/" title="SQLite">SQLite</a> is a software library that implements a self-contained, serverless, zero-configuration, transactional SQL database engine. The source code for SQLite is in the <a href="http://www.sqlite.org/copyright.html"> public domain</a>.');
$credits[] = array('en' => 1, 'title' => 'D3', 'desc' => '<a href="http://d3js.org/" title="D3.js">D3.js</a> is a JavaScript library for manipulating documents based on data. D3 helps you bring data to life using HTML, SVG and CSS. The source code for D3.js is in the <a href="https://github.com/mbostock/d3/blob/master/LICENSE"> public domain</a>.');
$credits[] = array('en' => 1, 'title' => 'PostgreSQL', 'desc' => '<a href="http://www.postgresql.org/" title="PostgreSQL">PostgreSQL</a> is a powerful, open source object-relational database system. The <a href="http://opensource.org/licenses/postgresql">PostgreSQL License</a> gives you the freedom to use, modify and distribute PostgreSQL in any form you like, open or closed source.');
$credits[] = array('en' => 1, 'title' => 'GeoLite databases', 'desc' => 'The GeoLite databases are distributed under the Creative Commons Attribution-ShareAlike 3.0 Unported License. <a href="http://www.maxmind.com">http://www.maxmind.com</a>.');
$credits[] = array('en' => 0, 'title' => '', 'desc' => '');
$credits[] = array('en' => 0, 'title' => '', 'desc' => '');
$credits[] = array('en' => 0, 'title' => '', 'desc' => '');
$credits_en = false;
foreach ($credits as $credit) {
	if ($credit['en']) {
		$credits_en = true;
		break;
	}
}

$attributions = array();
$attributions[] = array('en' => 1, 'title' => 'GeoLite databases', 'desc' => 'This product includes GeoLite data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>');
$attributions[] = array('en' => 0, 'title' => '', 'desc' => '');
$attributions_en = false;
foreach ($attributions as $attribution) {
	if ($attribution['en']) {
		$attributions_en = true;
		break;
	}
}

?>
<section>

<?php if ($credits_en) : ?>
<div class="row">
	<div class="span12">
        <h1>License</h1>
        <p>CapAnalysis is released under GNU General Public License, version 2.<br/>
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; version 2 of the License.<br/>
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.<br/>
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA or see <a href="http://www.gnu.org/licenses">http://www.gnu.org/licenses</a>.</p>
	</div>
</div>
<div class="row">
	<div class="span12">
		<div class="">
			<h1>Credits</h1>
			<p>This Web User Interface is realized with: </p>
		</div>
	</div>
</div>
<?php $i = 0;
foreach ($credits as $credit):
	$cls = '';
	if ($i%3 == 0 && $credit['en']):
	    $cls = '';
		if ($i != 0): ?>
	</div>
		<?php endif?>
	<div class="row">
	<?php endif?>
	<?php if ($credit['en']): $i++; ?>
	<div class="span4 <?php echo $cls;?>">
		<div class="">
			<h3><?php echo $credit['title'];?></h3>
			<p><?php echo $credit['desc'];?></p>
		</div>
	</div>
	<?php endif?>
<?php endforeach; ?>
<?php if ($i%3 != 0): ?>
	</div>
<?php endif?>
<?php endif?>


<?php if ($attributions_en) : ?>
<div class="row">
	<div class="span12">
		<div class="">
			<h1>Attribution</h1>
		</div>
	</div>
</div>
<?php $i = 0;
foreach ($attributions as $attribution):
	$cls = '';
	if ($i%5 == 0 && $attribution['en']):
	    $cls = '';
		if ($i != 0): ?>
	</div>
		<?php endif?>
	<div class="row">
	<?php endif?>
	<?php if ($attribution['en']): $i++; ?>
	<div class="span6 <?php echo $cls;?>">
		<div class="">
			<h3><?php echo $attribution['title'];?></h3>
			<p><?php echo $attribution['desc'];?></p>
		</div>
	</div>
	<?php endif?>
<?php endforeach; ?>
<?php if ($i%3 != 0): ?>
	</div>
<?php endif?>
</div>
<?php endif?>


</section>
