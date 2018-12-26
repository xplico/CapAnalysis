<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo 'CapAnalysis: '.$title_for_layout; ?>
	</title>
	
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');
        echo $this->Html->css('jquery-ui-1.8.21.custom');
        echo $this->Html->css('jquery.qtip.min');
        echo $this->Html->css('grid');
		echo $this->fetch('css');
        echo $this->Html->css('jui.capana');
        echo $this->Html->css('capana');

        echo $this->Html->script('jquery-1.7.2.min');
        echo $this->Html->script('jquery.cookie');
        echo $this->Html->script('jquery-ui-1.8.21.custom.min');
        echo $this->Html->script('capana');
        echo $this->Html->script('jquery.qtip.min');
        echo $this->Html->script('jquery.kontrol');
        echo $this->Html->script('jquery.form');
		echo $this->fetch('script');
        
		echo $this->fetch('meta');
	?>
	<script>
	$(function() {
		AlertShow();
	});
	</script>
</head>
<body>
    <?php if (substr($this->Session->read('vers'), -1) == 'b'): ?>
    <img id="devel_image" style="position: fixed; top: 0pt; right: 0pt; border: 0pt none; z-index: 100;" src="<?php echo $this->Html->url("/images/beta.png") ?>" alt="">
    <?php endif; ?>
	<div id="header">
		<div id="message_box">
			<div id="alert"></div>
		</div>
		<div id="header-persist">
			<div id="header-top">
			    <div class="container_12">
					<div id="logo" class="grid_6">
						<a href="#">CapAnalysis</a>
					</div>
					<div id="header-user" class="grid_6">
						<p>
						<?php echo $this->Html->link(__('Status'), '/capinstall'); ?>
						</p>
						<p>-</p>
						<p>
						<?php echo $this->Html->link(__('Manual'), '/capinstall/manual/intro'); ?>
						</p>
						<p>
						<?php if ($this->Session->read('user')): ?>
						<?php echo __('Welcome', true).' '.$session->read('user') ?>
						<?php else : ?>
						<?php echo __('Welcome Guest', true); ?>
						<?php endif; ?>:
						</p>
					</div>
				</div>
			</div>
			<div id="header-menu">
				<ul>
				<li class='item first'><?php echo $this->Html->link(__('Data Sets'), array('controller' => 'datasets', 'action' => 'index')); ?></li>
				<?php if (isset($dataset_nm)): ?>
				<li class='item'><?php echo $this->Html->link($dataset_nm, array('controller' => 'items', 'action' => 'index', $iid)); ?></li>
				<?php endif; ?>
				<?php if ($this->Session->read('user')): ?>
					<?php if (isset($menu_bar)):  ?>
						<li class='item blank first'></li>
						<?php foreach ($menu_bar as $mb_elem): ?>
						<li class='item'><?php echo $html->link($mb_elem['label'], $mb_elem['link']); ?></li>
						<?php endforeach; ?>
						<li class='item blank last'></li>
					<?php endif; ?>
				<?php endif; ?>
				<li class='item blank last'></li>
                <ul>
			</div>
		</div>
	</div>
	
	<div id="container">
		<div id="content" class="container_12">
			<?php echo $this->fetch('content'); ?>
		</div>
	</div>
	
	<div id="footer" class="container_12">
	<p id="autor">CapAnalysis <?php echo $this->Session->read('vers'); ?>- <a href="http://www.capanalysis.net">http://www.capanalysis.net</a> - &copy; 2012-2018. All rights reserved.</p>
	</div>
	<?php echo $this->Session->flash(); ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
