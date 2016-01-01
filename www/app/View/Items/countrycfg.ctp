<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="countrycfg" class="mbody">
	<?php
	echo $this->Form->create('Item', array('action' => 'countrycfg'));
	echo $this->Form->select('country', $fields, array('multiple' => 'checkbox', 'value' => $selected));
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
	$('#countrycfg .checkbox label').each(function() {
		var str = $(this).text();
		$(this).html(CountryName(str));
	});

	var countrycfg = true;
	$('#countrycfg .cbutton').unbind('click');
	$('#countrycfg .cbutton').click(function() {
		if (countrycfg)
			$('#countrycfg .checkbox input:checkbox').attr('checked', false);
		else
			$('#countrycfg .checkbox input:checkbox').attr('checked', true);
		countrycfg = !countrycfg;
	});
	
</script>
