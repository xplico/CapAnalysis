<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php
$this->Html->script('raphael-min', array('inline' => false));
$this->Html->script('g.raphael-min', array('inline' => false));
$this->Html->script('g.pie-min', array('inline' => false));
?>
<div class="grid_10">
<div id="tabs" class="tabs-nohdr tbl">
	<ul>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'datasets', 'action' => 'dataset')); ?>">Datasets</a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'datasets', 'action' => 'add')); ?>" title="<?php echo __('Add new DataSet'); ?>"><?php echo __('+ New'); ?></a></li>
        <?php if ($this->Session->check('demo')): ?>
        <li><a href="<?php echo $this->Html->url(array('controller' => 'datasets', 'action' => 'rules')); ?>" title="<?php echo __('Demo rules'); ?>"><?php echo __('Rules'); ?></a></li>
        <?php endif; ?>
    </ul>
</div>
</div>

<div class="grid_2">
    <div class="display round shadow first">
		<div class="in inshadow">
			<div id="ds_num" class="displ-count"> <?php echo $ds_count; ?> </div>
			<div class="displ-title"> DataSets </div>
		</div>
    </div>
    <div class="display round shadow">
		<div class="in inshadow">
			<div class="displ-count"> <?php echo $file_count; ?> </div>
			<div class="displ-title"> <?php echo __('Files'); ?> </div>
		</div>
    </div>
    <div class="display round shadow">
		<div class="in inshadow">
			<div class="displ-count"> <?php echo $file_size; ?> </div>
			<div class="displ-title"> <?php echo __('Size'); ?> </div>
		</div>
    </div>
</div>
<script>
$(function() {
	$('li a[title]').qtip({position: {my: 'bottom center', at: 'top center'}, style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'}});
    $("#tabs").tabs({
    <?php if (isset($demo_info)): ?>
        selected: 2
    <?php endif; ?>
    });
    //$("#tabs").tabs( "option", "active", 2 );
	$('.actions a').button();
});
</script>
