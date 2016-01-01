<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<script>
	$('.ipdata').remove();
</script>

<div class="ipdata_last">
	<div class="tabsmpl">
		<ul>
			<li><a href="#iptabs-1"><?php echo __('As Source'); ?></a></li>
			<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdatad', $ip_id)); ?>"><?php echo __('As Destination'); ?></a></li>
			<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'connect', $ip_id)); ?>"><?php echo __('Connections'); ?></a></li>
			<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'timeline', $ip_id)); ?>"><?php echo __('TimeLine'); ?></a></li>
			<li><a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'whois', $ip_id)); ?>"><?php echo __('Whois'); ?></a></li>
		</ul>
		<div id="iptabs-1">
			<div class="floatl">
				<p><strong><?php echo __('First connection').': <span class="data">'.$fist_s_con['Item']['cdate'].' '.$fist_s_con['Item']['ctime'].'</span>';?></strong></p>
				<p><strong><?php echo __('Last connection').': <span class="data">'.$last_s_con['Item']['cdate'].' '.$last_s_con['Item']['ctime'].'</span>';?></strong></p>
				<p class="donut_cng a" data-neme="f"><strong><?php echo __('Flows').': <span class="data">'.$flows_s_cnt[0][0]['fcnt'].'</span>';?></strong></p>
				<p class="donut_cng a" data-neme="t"><strong><?php echo __('Data').': <span class="data">'.$this->String->size($flows_s_cnt[0][0]['bout']+$flows_s_cnt[0][0]['bin']).'</span>';?></strong></p>
				<div class="donut_cng a" data-neme="i">
				<p><strong><?php echo __('Data In').': <span class="data">'.$this->String->size($flows_s_cnt[0][0]['bin']).'</span>';?></strong></p>
				<p><strong><?php echo __('Data Out').': <span class="data">'.$this->String->size($flows_s_cnt[0][0]['bout']).'</span>';?></strong></p>
				</div>
				<p><strong><?php echo __('IP contacted').': <span class="data">'.$this->String->num($ipd_cnt).'</span>';?></strong></p>
			</div>
			<div class="floatl">
				<div id="donut_ip2tot" class="donut-chart floatl"></div>
				<div id="donut_ip2dat" class="donut-chart floatl"></div>
			</div>
			<div class="clear">&nbsp;</div>
			<hr>
			<div class="centered">
			<p class="donut_cng b inline" data-neme="f"><strong><?php echo __('Flows');?></strong></p>
			<p class="donut_cng b mleft inline" data-neme="t"><strong><?php echo __('Data');?></strong></p>
			<p class="donut_cng b mleft inline" data-neme="i"><strong><?php echo __('Data In');?></strong></p>
			<p class="donut_cng b mleft inline" data-neme="o"><strong><?php echo __('Data Out');?></strong></p>
			</div>
			<div class="clear">&nbsp;</div>
			<div class="floatl">
				<div id="donut_ip2prot" class="donut-chart"></div>
				<p class="centered"><strong><?php echo __('Protocols');?></strong></p>
			</div>
			<div class="floatl">
				<div id="donut_ip2cntr" class="donut-chart"></div>
				<p class="centered"><strong><?php echo __('Country');?></strong></p>
			</div>
			<div class="floatl">
				<div id="ip2hours"></div>
				<p class="centered"><strong><?php echo __('Hours');?></strong></p>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
</div>

<script>
	$('.ipdata_last').toggleClass('ipdata_last').addClass('ipdata');
	//tabs
	$('.tabsmpl').tabs('destroy').tabs();
	$('.tabsmpl').tabs({
		cache: true
	});

	// data
	var geom = {w: 280, h: 180, r: 55, ir: 35, unit:"<?php echo __('Flows');?>", base: 1000},
		geom1 = {w: 310, h: 180, r: 55, ir: 35, unit:"<?php echo __('Byte');?>", base: 1024},
		geom2 = {w: 280, h: 180, r: 80, ir: 35, unit:"<?php echo __('Flows');?>", base: 1000},
		basuni = {
			f: {b: 1000, u: '<?php echo __('Flows');?>'},
			i: {b: 1024, u: '<?php echo __('Byte');?>'},
			o: {b: 1024, u: '<?php echo __('Byte');?>'},
			t: {b: 1024, u: '<?php echo __('Byte');?>'}
		};
		
	var data = {
			f: [{n: '<?php echo __('This IP');?>', val: <?php echo $flows_s_cnt[0][0]['fcnt'];?>}, {n: '<?php echo __('Other');?>', val: <?php echo $flows_cnt[0][0]['fcnt']-$flows_s_cnt[0][0]['fcnt'];?>}],
			i: [{n: '<?php echo __('Data Sent');?>', val: <?php echo $flows_s_cnt[0][0]['bout'];?>}, {n: '<?php echo __('Data Received');?>', val: <?php echo $flows_s_cnt[0][0]['bin'];?>}],
			t: [{n: '<?php echo __('This IP Data');?>', val: <?php echo $flows_s_cnt[0][0]['bout']+$flows_s_cnt[0][0]['bin'];?>}, {n: '<?php echo __('Other IPs');?>', val: <?php echo $flows_cnt[0][0]['bin']+$flows_cnt[0][0]['bout']-$flows_s_cnt[0][0]['bin']-$flows_s_cnt[0][0]['bout'];?>}],
		},
		ip2tot = donut("#donut_ip2tot", data.f, geom),
		ip2dat = donut("#donut_ip2dat", data.i, geom1);
	
	$("#iptabs-1 .donut_cng.a").unbind('mouseover');
	$("#iptabs-1 .donut_cng.a").mouseover(function() {
		var tp = $(this).attr("data-neme");
		ip2tot.base(basuni[tp].b);
		ip2tot(data[tp]);
		ip2tot.unit(basuni[tp].u);
	});

	// protocol, country, hours ...
	var datprot = undefined,
		datcntr = undefined,
		dathours = undefined,
		ip2prot = donut("#donut_ip2prot", datprot, geom),
		ip2cntr = donut("#donut_ip2cntr", datprot, geom),
		ip2hours = chours("#ip2hours", dathours, geom2);
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdjsn', 'src', $ip_id, 'sprot.json')); ?>", function(json) {
		datprot = json;
		ip2prot(datprot.f);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdjsn', 'src', $ip_id, 'cntr.json')); ?>", function(json) {
		datcntr = json;
		ip2cntr(datcntr.f);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdjsn', 'src', $ip_id, 'hours.json')); ?>", function(json) {
		dathours = json;
		ip2hours(dathours.f);
	});
	$("#iptabs-1 .donut_cng.b").unbind('mouseover');
	$("#iptabs-1 .donut_cng.b").mouseover(function() {
		var tp = $(this).attr("data-neme");
		ip2prot.base(basuni[tp].b);
		ip2prot(datprot[tp]);
		ip2prot.unit(basuni[tp].u);
		ip2cntr.base(basuni[tp].b);
		ip2cntr(datcntr[tp]);
		ip2cntr.unit(basuni[tp].u);
		ip2hours.base(basuni[tp].b);
		ip2hours(dathours[tp]);
		ip2hours.unit(basuni[tp].u);
	});
	
</script>
