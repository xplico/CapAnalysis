<!--
   CapAnalysis

   Copyright 2012-2016 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php if ($added): ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __('DataSet added.'); ?></h2>
	</div>
</div>
<script>
	$('#ds_num').text(<?php echo $ds_count; ?>);
</script>
<?php elseif ($this->Session->check('trial') && $ds_count >= 1): ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __('CapAnalysis-Trial is limited to One DataSet.'); ?></h2>
	</div>
</div>
<?php elseif ($this->Session->check('demo')): ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __('CapAnalysis Demo is limited to one DataSet'); ?></h2>
	</div>
</div>
<?php else : ?>
<div class="outcome">
	<div class="outcome-bord" id="newdataset">
		<div class="form">
			<?php echo $this->Form->create('Dataset', array('action' => 'add', 'id'=>'jform')); ?>
			<h2><?php echo __('DataSet'); ?></h2><br/>
			<?php echo $this->Form->input('name', array('maxlength'=>'40', 'label' => __('Name').': ')); ?>
            <?php echo $this->Form->input('depth', array('label' => __('Depth').': ',
                'options' => array(__('None'), __('End of Life'))
            )); ?>
            <?php echo $this->Form->input('eol', array('maxlength'=>'11', 'label' => __('End Date').': ', 'div' => array('id' => 'eolv', 'class' => 'dispoff'), 'id' => 'eol', 'class' => 'date')); ?>
			<?php echo $this->Form->end(__('Submit')); ?><br/>
		</div>
	</div>
</div>
<script>
	$('#ds_num').text(<?php echo $ds_count; ?>);
    
	$('#jform').ajaxForm({ 
		target: $('#jform').parent().parent().parent()
	});
    
	$("#eol").datepicker({
		dateFormat: "yy-mm-dd",
		changeMonth: true,
		changeYear: true
	});
    
    switch ($("#DatasetDepth").val()) {
    case '1': $('#eolv').show(); break;
    case '2': $('#tdv').show(); break;
    case '3': $('#fdv').show(); break;
    case '4': $('#szv').show(); break;
    }

    $("#DatasetDepth").change(function() {
        $('#eolv').hide();
        $('#tdv').hide();
        $('#fdv').hide();
        $('#szv').hide();
        switch ($(this).val()) {
        case '1': $('#eolv').show(); break;
        case '2': $('#tdv').show(); break;
        case '3': $('#fdv').show(); break;
        case '4': $('#szv').show(); break;
        }
    });
	
	$('input:submit').button();
</script>
<?php endif; ?>
