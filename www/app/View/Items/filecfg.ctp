<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="filecfg" class="mbody">
	<?php
	echo $this->Form->create('Item', array('url' => 'filecfg'));
	echo $this->Form->select('capfiles', $fields, array('multiple' => 'checkbox', 'value' => $selected));
	?>
	<div class="clear">&nbsp;</div>
	<?php echo $this->Form->end(__('Apply')); ?>
	<div class="clear">&nbsp;</div>
	<div class="cbutton">
	<?php echo __('Select/Unselect All'); ?>
	</div>
</div>
<script>
	$('input:submit').button();
	var filecfg = true;
	$('#filecfg .cbutton').unbind('click');
	$('#filecfg .cbutton').click(function() {
		if (filecfg)
			$('#filecfg .checkbox input:checkbox').attr('checked', false);
		else
			$('#filecfg .checkbox input:checkbox').attr('checked', true);
		filecfg = !filecfg;
	});
</script>
