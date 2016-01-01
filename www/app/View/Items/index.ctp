<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php
$this->Html->script('jquery.sparkline.min', array('inline' => false));
$this->Html->script('jquery-ui-timepicker-addon', array('inline' => false));
$this->Html->script('d3.v2.min', array('inline' => false));
$this->Html->script('bar-stack-chart', array('inline' => false));
$this->Html->script('sankey', array('inline' => false));
$this->Html->script('leaflet', array('inline' => false));
$this->Html->script('world-countries', array('inline' => false));
$this->Html->css('infowhois', null, array('inline' => false));
$this->Html->css('jquery-ui-timepicker-addon', null, array('inline' => false));
$this->Html->css('bar-stack-chart', null, array('inline' => false));
$this->Html->css('leaflet', null, array('inline' => false));
?>
<div class="grid_11 suffix_1">
<div id="tabs" class="tabs-nohdr tbl">
	<ul>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'items')); ?>" data-title="<?php echo __('TCP and UDP data'); ?>"><?php echo __('Flows').' ['.$this->String->num($items_num).']'; ?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'globalview')); ?>" data-title="<?php echo __('Overview'); ?>"><?php echo __('Overview'); ?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'statistics')); ?>" data-title="<?php echo __('View some statistis charts'); ?>"><?php echo __('Statistcs');?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'overview')); ?>" data-title="<?php echo __('Overview per hour'); ?>"><?php echo __('Per Hour');?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'geomap')); ?>" data-title="<?php echo __('World Map'); ?>"><?php echo __('GeoMAP');?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipsource')); ?>" data-title="<?php echo __('All IPs sources of connections'); ?>"><?php echo __('IPs Source').' ['.$this->String->num($ips_num).']';?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdestin')); ?>" data-title="<?php echo __('All IPs destination of connections'); ?>"><?php echo __('IPs Destination').' ['.$this->String->num($ipd_num).']';?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'protocols')); ?>" data-title="<?php echo __('Protocols View'); ?>"><?php echo __('Protocols');?></a></li>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'timeline')); ?>" data-title="<?php echo __('Time Line View'); ?>"><?php echo __('Timeline');?></a></li>
	</ul>
	<div class="clear">&nbsp;</div>
</div>
</div>
    
<div class="clear">&nbsp;</div>
<div id="mlatbar">
<div id="tabs-right" class="tabs-nohdr">
	<ul>
		<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'viewcfg')); ?>"><?php echo $this->Html->image('view.png', array('alt' => '', 'title' => __('View elements'))); ?></a></li>
		<li class="<?php if ($cfgf[0]) echo 'active';?>"><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'filecfg')); ?>"><?php echo $this->Html->image('files.png', array('alt' => '', 'title' => __('Files'))); ?></a></li>
		<li class="<?php if ($cfgf[1]) echo 'active';?>"><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'netcfg')); ?>"><?php echo $this->Html->image('ip.png', array('alt' => '', 'title' => __('IPs and Ports'))); ?></a></li>
		<li class="<?php if ($cfgf[2]) echo 'active';?>"><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'protocfg')); ?>"><?php echo $this->Html->image('protocols.png', array('alt' => '', 'title' => __('Protocols'))); ?></a></li>
		<li class="<?php if ($cfgf[3]) echo 'active';?>"><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'countrycfg')); ?>"><?php echo $this->Html->image('country.png', array('alt' => '', 'title' => __('Country'))); ?></a></li>
		<li class="<?php if ($cfgf[4]) echo 'active';?>"><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'datacfg')); ?>"><?php echo $this->Html->image('data.png', array('alt' => '', 'title' => __('Data Size'))); ?></a></li>
		<li class="<?php if ($cfgf[5]) echo 'active';?>"><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'timecfg')); ?>"><?php echo $this->Html->image('time.png', array('alt' => '', 'title' => __('Time'))); ?></a></li>
	</ul>
</div>
</div>

<script>
$(function() {
	//tabs
	$("#tabs").tabs({
		cookie: {expires: 1},
		cache: true 
	});
	// lateral tabs
	$("#tabs-right").tabs({
			collapsible: true,
			selected: -1,
			fx: { opacity: 'toggle'}
	});	
	$("#tabs-right .tabw").addClass("ui-tabs-hide");
	// tip
	$('li a[data-title]').qtip({
		position: {my: 'bottom center', at: 'top center'},
		style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'},
		content: {
			text: function(api) {
				return $(this).attr('data-title');
			}
		}
	});
	$('img[title]').qtip({position: {my: 'right center', at: 'center left'}, style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'}, show: {delay: 700}});
});
</script>
