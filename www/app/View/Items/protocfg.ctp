<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="protocfg" class="mbody">
	<?php
	echo $this->Form->create('Item', array('action' => 'protocfg'));
	echo $this->Form->select('proto', $fields, array('multiple' => 'checkbox', 'value' => $selected));
	?>
	<div class="clear">&nbsp;</div>
	<?php echo $this->Form->end(__('Apply')); ?>
	<div class="clear">&nbsp;</div>
	<div class="cbutton">
	<?php echo __('Select/Unselect All'); ?>
	</div>
</div>
<script>
	$('#protocfg input:submit').button();
	var protocfg = true;
	$('#protocfg .cbutton').unbind('click');
	$('#protocfg .cbutton').click(function() {
		if (protocfg)
			$('#protocfg .checkbox input:checkbox').attr('checked', false);
		else
			$('#protocfg .checkbox input:checkbox').attr('checked', true);
		protocfg = !protocfg;
	});
</script>
