<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="netcfg" class="mbody">
	<?php if ($nrules) : ?>
	<div id="rules" class="floatl">
		<h3><?php echo __('Rules active:');?></h3>
		<?php $img_op = $this->Html->image('or.png', array('alt' => '')); ?>
		<?php $img_out = $this->Html->image('out.png', array('alt' => '')); ?>
		<?php $img_in = $this->Html->image('in.png', array('alt' => '')); ?>
		<?php $img_rm = $this->Html->image('rm.png', array('alt' => '', 'class' => 'nrulerm cursor')); ?>
		<?php foreach ($nrules[0] as $id => $rl): ?>
			<div id="rl<?php echo $id; ?>" data-id="<?php echo $id; ?>" data-tp="0" class="rlbox">
			<?php
			echo $img_op;
			if ($rl[0] == 'id' or $rl[0] == 'pd' or $rl[0] == 'nd')
				echo $img_out;
			else
				echo $img_in;
			?>
			<span><?php echo $rl[1]; ?></span>
			<?php echo $img_rm; ?>
			</div>
		<?php endforeach;?>
		<?php $img_op = $this->Html->image('and.png', array('alt' => '')); ?>
		<?php foreach ($nrules[1] as $id => $rl): ?>
			<div id="rl<?php echo $id; ?>" data-id="<?php echo $id; ?>" data-tp="1" class="rlbox">
			<?php
			echo $img_op;
			if ($rl[0] == 'id' or $rl[0] == 'pd' or $rl[0] == 'nd')
				echo $img_out;
			else
				echo $img_in;
			?>
			<span><?php echo $rl[1]; ?></span>
			<?php echo $img_rm; ?>
			</div>
		<?php endforeach;?>
		<div class="clear">&nbsp;</div>
	</div>
	<?php endif; ?>
	<div class="floatr">
		<?php echo $this->Form->create('Item', array('url' => 'netcfg')); ?>
		<h3><?php echo __('Filter Type:');?></h3>
		<div class="floatl grid_4 ">
		<?php echo $this->Form->radio('type', array(__('OR'), __('AND')), array('value' => 1, 'legend' => false));?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl mright grid_2">
		<h3><?php echo __('Source IP').':';?></h3>
		<?php echo $this->Form->input('netsip', array('maxlength'=>'40', 'label' => false, 'class' => 'date')); ?>
		</div>
		<div class="floatl grid_2">
		<h3><?php echo __('Destination IP').':';?></h3>
		<?php echo $this->Form->input('netdip', array('maxlength'=>'40', 'label' => false, 'class' => 'date')); ?>
		</div>
		<div class="floatr grid_2">
		<h3><?php echo __('Destination Name').':';?></h3>
		<?php echo $this->Form->input('netdname', array('maxlength'=>'40', 'label' => false, 'class' => 'date')); ?>
		</div>
		<div class="clear">&nbsp;</div>
		<div class="floatl mright grid_2">
		<h3><?php echo __('Source Port').':';?></h3>
		<?php echo $this->Form->input('netsport', array('maxlength'=>'40', 'label' => false, 'class' => 'date')); ?>
		</div>
		<div class="floatl grid_2">
		<h3><?php echo __('Destination Port').':';?></h3>
		<?php echo $this->Form->input('netdport', array('maxlength'=>'40', 'label' => false, 'class' => 'date')); ?>
		</div>
		<div class="clear">&nbsp;</div>
		<?php echo $this->Form->end(__('Apply')); ?>
		<div class="clear">&nbsp;</div>
	</div>
	<div class="clear">&nbsp;</div>
</div>
<script>
	$('#netcfg input:submit').button();
	$('#rules .rlbox img.nrulerm').click(function() {
		var box = $(this).parent();
		var id = box.attr('data-id');
		var tp = box.attr('data-tp');
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'netcfg_rm')); ?>"+'/'+tp+'/'+id,
			context: document.body
		}).done(function() { 
			box.fadeOut();
		});
	});
</script>
