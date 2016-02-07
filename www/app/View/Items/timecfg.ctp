<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="timecfg" class="mbody">
	<?php if ($nrules) : ?>
	<div id="rules" class="floatl">
		<h3><?php echo __('Rules active:');?></h3>
		<?php
			$img_op = $this->Html->image('or.png', array('alt' => ''));
			$img_out = $this->Html->image('out.png', array('alt' => ''));
			$img_in = $this->Html->image('in.png', array('alt' => ''));
			$img_eq = $this->Html->image('eq.png', array('alt' => ''));
			$img_rm = $this->Html->image('rm.png', array('alt' => '', 'class' => 'nrulerm cursor'));
		 ?>
		<?php foreach ($nrules[0] as $id => $rl): ?>
			<div id="rl<?php echo $id; ?>" data-id="<?php echo $id; ?>" data-tp="0" class="rlbox">
			<?php
			echo $img_op;
			if ($rl[0] == 'dt' or $rl[0] == 'tt') {
				echo $img_in;
				echo '<span>'.$rl[1].'</span>';
			}
			else if ($rl[0] == 'de') {
				echo $img_eq;
				echo '<span>'.$rl[1].'</span>';
			}
			else {
				echo '<span>'.$rl[1].'</span>';
				echo $img_out;
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
			if ($rl[0] == 'dt' or $rl[0] == 'tt') {
				echo $img_in;
				echo '<span>'.$rl[1].'</span>';
			}
			else if ($rl[0] == 'de') {
				echo $img_eq;
				echo '<span>'.$rl[1].'</span>';
			}
			else {
				echo '<span>'.$rl[1].'</span>';
				echo $img_out;
			}
			?>
			<?php echo $img_rm; ?>
			</div>
		<?php endforeach;?>
		<div class="clear">&nbsp;</div>
	</div>
	<?php endif; ?>
	<div class="floatr">
		<?php echo $this->Form->create('Item', array('url' => 'timecfg')); ?>
		<div class="floatl grid_4">
		<h3><?php echo __('Filter Type:');?></h3>
		<?php echo $this->Form->radio('type', array(__('OR'), __('AND')), array('value' => 1, 'legend' => false));?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl mright grid_2">
		<h3><?php echo __('From Date').':';?></h3>
		<?php echo $this->Form->input('dfrom', array('maxlength'=>'10', 'label' => false, 'id' => 'dfrom', 'class' => 'date')); ?>
		</div>
		<div class="floatl grid_2">
		<h3><?php echo __('To Date').':';?></h3>
		<?php echo $this->Form->input('dto', array('maxlength'=>'10', 'label' => false, 'id' => 'dto', 'class' => 'date')); ?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl mright grid_2">
		<h3><?php echo __('From Hour').':';?></h3>
		<?php echo $this->Form->input('tfrom', array('maxlength'=>'10', 'label' => false, 'id' => 'tfrom', 'class' => 'date noborder')); ?>
		</div>
		<div class="floatl grid_2">
		<h3><?php echo __('To Hour').':';?></h3>
		<?php echo $this->Form->input('tto', array('maxlength'=>'10', 'label' => false, 'id' => 'tto', 'class' => 'date noborder')); ?>
		</div>
		<div class="clear">&nbsp;</div>
		<?php echo $this->Form->end(__('Apply')); ?>
		<div class="clear">&nbsp;</div>
	</div>
	<div class="clear">&nbsp;</div>
</div>
<script>
	$('input:submit').button();
	$("#dfrom").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
		onSelect: function( selectedDate ) {
			$("#dto").datepicker();
		}
	});
	$("#dto").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true,
		onSelect: function( selectedDate ) {
			$("#dfrom").datepicker();
		}
	});
	$("#tto").timepicker({
		hour: 23,
		minute: 59
	});
	$("#tfrom").timepicker({
		hour: 0,
		minute: 0
	});
	$('#rules .rlbox img.nrulerm').click(function() {
		var box = $(this).parent();
		var id = box.attr('data-id');
		var tp = box.attr('data-tp');
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'timecfg_rm')); ?>"+'/'+tp+'/'+id,
			context: document.body
		}).done(function() { 
			box.fadeOut();
		});
	});
</script>
