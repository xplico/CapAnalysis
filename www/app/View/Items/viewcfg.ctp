<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->

<div id="viewcfg" class="mbody">
	<?php
	echo $this->Form->create('Item', array('url' => 'viewcfg'));
	echo $this->Form->select('columns', $fields, array('multiple' => 'checkbox', 'value' => $selected));
	?>
	<div class="clear">&nbsp;</div>
	<div class="cbutton floatl">
	<?php echo __('Select/Unselect All'); ?>
	</div>
	<div class="floatl">
	<?php echo $this->Form->input('limit_row', array('maxlength'=>'4', 'label' => __('Rows').':', 'value' => $limit_row)); ?>
	</div>
	<?php echo $this->Form->end(__('Apply')); ?>
	<div class="clear">&nbsp;</div>
	<div class="clear">&nbsp;</div>
</div>
<script>
	$('#viewcfg input:submit').button();
	var viewcfg = true;
	$('#viewcfg .cbutton').unbind('click');
	$('#viewcfg .cbutton').click(function() {
		if (viewcfg)
			$('#viewcfg .checkbox input:checkbox').attr('checked', false);
		else
			$('#viewcfg .checkbox input:checkbox').attr('checked', true);
		viewcfg = !viewcfg;
	});
</script>
