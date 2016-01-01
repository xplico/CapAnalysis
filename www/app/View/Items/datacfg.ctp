<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="datacfg" class="mbody">
	<?php if ($nrules) : ?>
	<div id="rules" class="floatl">
		<h3><?php echo __('Rules active:');?></h3>
		<?php $img_op = $this->Html->image('or.png', array('alt' => '')); ?>
		<?php $img_sent = $this->Html->image('sent.png', array('alt' => '')); ?>
		<?php $img_rec = $this->Html->image('receiv.png', array('alt' => '')); ?>
		<?php $img_eq = $this->Html->image('eq.png', array('alt' => '')); ?>
		<?php $img_min = $this->Html->image('min.png', array('alt' => '')); ?>
		<?php $img_maj = $this->Html->image('maj.png', array('alt' => '')); ?>
		<?php $img_rm = $this->Html->image('rm.png', array('alt' => '', 'class' => 'nrulerm cursor')); ?>
		<?php foreach ($nrules[0] as $id => $rl): ?>
			<div id="rl<?php echo $id; ?>" data-id="<?php echo $id; ?>" data-tp="0" class="rlbox">
			<?php
			echo $img_op;
			if ($rl[0] == 'dsr') {
				echo $img_sent;
				if ($rl[1] == 0) {
					echo $img_maj;
				}
				if ($rl[1] == 1) {
					echo $img_eq;
				}
				if ($rl[1] == 2) {
					echo $img_min;
				}
				echo $img_rec;
			}
			if ($rl[0] == 'ds') {
				echo $img_sent;
				if ($rl[1] == 0) {
					echo $img_maj;
				}
				if ($rl[1] == 1) {
					echo $img_eq;
				}
				if ($rl[1] == 2) {
					echo $img_min;
				}
				echo '<span>'.$rl[2].'</span>';
			}
			if ($rl[0] == 'dr') {
				echo $img_rec;
				if ($rl[1] == 0) {
					echo $img_maj;
				}
				if ($rl[1] == 1) {
					echo $img_eq;
				}
				if ($rl[1] == 2) {
					echo $img_min;
				}
				echo '<span>'.$rl[2].'</span>';
			}
			?>
			<?php echo $img_rm; ?>
			</div>
		<?php endforeach;?>
		<?php $img_op = $this->Html->image('and.png', array('alt' => '')); ?>
		<?php foreach ($nrules[1] as $id => $rl): ?>
			<div id="rl<?php echo $id; ?>" data-id="<?php echo $id; ?>" data-tp="1" class="rlbox">
			<?php
			echo $img_op;
			if ($rl[0] == 'dsr') {
				echo $img_sent;
				if ($rl[1] == 0) {
					echo $img_maj;
				}
				if ($rl[1] == 1) {
					echo $img_eq;
				}
				if ($rl[1] == 2) {
					echo $img_min;
				}
				echo $img_rec;
			}
			if ($rl[0] == 'ds') {
				echo $img_sent;
				if ($rl[1] == 0) {
					echo $img_maj;
				}
				if ($rl[1] == 1) {
					echo $img_eq;
				}
				if ($rl[1] == 2) {
					echo $img_min;
				}
				echo '<span>'.$rl[2].'</span>';
			}
			if ($rl[0] == 'dr') {
				echo $img_rec;
				if ($rl[1] == 0) {
					echo $img_maj;
				}
				if ($rl[1] == 1) {
					echo $img_eq;
				}
				if ($rl[1] == 2) {
					echo $img_min;
				}
				echo '<span>'.$rl[2].'</span>';
			}
			?>
			<?php echo $img_rm; ?>
			</div>
		<?php endforeach;?>
		<div class="clear">&nbsp;</div>
	</div>
	<?php endif; ?>
	<div class="floatr">
		<?php echo $this->Form->create('Item', array('action' => 'datacfg')); ?>
		<div class="floatl grid_4">
		<h3><?php echo __('Filter Type:');?></h3>
		<?php echo $this->Form->radio('type', array(__('OR'), __('AND')), array('value' => 1, 'legend' => false));?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl mright grid_4">
		<h3><?php echo __('Data Sent').':';?></h3>
		<?php echo __('Sent');?> <?php echo $this->Form->radio('dso', array(__('>'), __('='), __('<')), array('legend' => false));?>
		<?php echo $this->Form->input('dss', array('maxlength'=>'10', 'label' => false, 'div' => 'floatr', 'class' => 'date')); ?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl mright grid_4">
		<h3><?php echo __('Data Received').':';?></h3>
		<?php echo __('Received');?> <?php echo $this->Form->radio('dro', array(__('>'), __('='), __('<')), array('legend' => false));?>
		<?php echo $this->Form->input('drs', array('maxlength'=>'10', 'label' => false, 'div' => 'floatr', 'class' => 'date')); ?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl grid_4">
		<h3><?php echo __('Data Sent vs Data Received').':';?></h3>
		<p><?php echo __('Sent').' '.$this->Form->radio('dsr', array(__('>'), __('='), __('<')), array('legend' => false)).' '.__('Received');?></p>
		</div>
		<div class="clear">&nbsp;</div>
		<?php echo $this->Form->end(__('Apply')); ?>
		<div class="clear">&nbsp;</div>
	</div>
	<div class="clear">&nbsp;</div>
</div>
<script>
	$('input:submit').button();
	$('#rules .rlbox img.nrulerm').click(function() {
		var box = $(this).parent();
		var id = box.attr('data-id');
		var tp = box.attr('data-tp');
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'datacfg_rm')); ?>"+'/'+tp+'/'+id,
			context: document.body
		}).done(function() { 
			box.fadeOut();
		});
	});
</script>
