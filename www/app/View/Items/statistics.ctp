<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div style="position: absolute; right: 0;">
	<div id="reload_stts" class="treload"><a href="#"><?php echo $this->Html->image('reload.png', array('alt' => '')); ?></a></div>
</div>
<div id="statistics">
	<div class="floatl cdetails stat_size">
		<h3><?php echo __('Source IP vs Flows'); ?></h3>
		<div id="stat_ipsf"></div>
	</div>
	<div class="floatr cdetails stat_size">
		<h3><?php echo __('Source IP vs Data Sent and Received'); ?></h3>
		<div id="stat_ipsd"></div>
	</div>
	<div class="clear">&nbsp;</div>
	<div class="floatl cdetails stat_size">
		<h3><?php echo __('Destination IP vs Flows'); ?></h3>
		<div id="stat_ipdf"></div>
	</div>
	<div class="floatr cdetails stat_size">
		<h3><?php echo __('Destination IP vs  Data Sent and Received'); ?></h3>
		<div id="stat_ipdd"></div>
	</div>
	<div class="clear">&nbsp;</div>
	<div class="floatl cdetails stat_size">
		<h3><?php echo __('Protocols vs Flows'); ?></h3>
		<div id="stat_prot"></div>
	</div>
	<div class="floatr cdetails stat_size">
		<h3><?php echo __('Country vs Flows'); ?></h3>
		<div id="stat_country"></div>
	</div>
	<div class="clear">&nbsp;</div>
	<div class="floatl cdetails mleft mright">
		<h3><?php echo __('Duration vs Flows'); ?></h3>
		<div id="stat_dur" class="floatl"></div>
	</div>
	<script>
	var margin = {top: 20, right: 30, bottom: 10, left: 140},
		stat_ipsf = barStackChart(),
		stat_ipsd = barStackChart(),
		stat_ipdf = barStackChart(),
		stat_ipdd = barStackChart(),
		stat_prot = barStackChart(),
		stat_count = barStackChart(),
		stat_dur = barStackChart();
	stat_ipsf.margin(margin).delay(500);
	stat_ipsd.margin(margin).delay(500);
	stat_ipdf.margin(margin).delay(700);
	stat_ipdd.margin(margin).delay(700);
	margin.left = 80;
	stat_prot.margin(margin).delay(1200);
	stat_count.delay(1200);
	stat_dur.width(860).sort(false).delay(1200);
	
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'ipsf.json')); ?>", function(json) {
		d3.select("#stat_ipsf").datum(json)
			.call(stat_ipsf);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'ipsd.json')); ?>", function(json) {
		d3.select("#stat_ipsd").datum(json)
			.call(stat_ipsd);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'ipdf.json')); ?>", function(json) {
		d3.select("#stat_ipdf").datum(json)
			.call(stat_ipdf);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'ipdd.json')); ?>", function(json) {
		d3.select("#stat_ipdd").datum(json)
			.call(stat_ipdd);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'prot.json')); ?>", function(json) {
		d3.select("#stat_prot").datum(json)
			.call(stat_prot);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'country.json')); ?>", function(json) {
		d3.select("#stat_country").datum(json)
			.call(stat_count);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'stat_ip', 'duration.json')); ?>", function(json) {
		d3.select("#stat_dur").datum(json)
			.call(stat_dur);
	});
	// reload
	$('#reload_stts').unbind('click');
	$("#reload_stts").click(function() {
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'statistics')); ?>",
			context: document.body
		}).done(function(data) { 
			$("#ui-tabs-3").html(data);
		});
	});
	</script>
</div>
