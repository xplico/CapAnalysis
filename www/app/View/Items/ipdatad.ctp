<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
			<div class="floatl">
				<p><strong><?php echo __('First connection').': <span class="data">'.$fist_d_con['Item']['cdate'].' '.$fist_d_con['Item']['ctime'].'</span>';?></strong></p>
				<p><strong><?php echo __('Last connection').': <span class="data">'.$last_d_con['Item']['cdate'].' '.$last_d_con['Item']['ctime'].'</span>';?></strong></p>
				<p class="donut_cng c" data-neme="f"><strong><?php echo __('Flows').': <span class="data">'.$flows_d_cnt[0][0]['fcnt'].'</span>';?></strong></p>
				<p class="donut_cng c" data-neme="t"><strong><?php echo __('Data').': <span class="data">'.$this->String->size($flows_d_cnt[0][0]['bout']+$flows_d_cnt[0][0]['bin']).'</span>';?></strong></p>
				<div class="donut_cng c" data-neme="i">
				<p><strong><?php echo __('Data In').': <span class="data">'.$this->String->size($flows_d_cnt[0][0]['bin']).'</span>';?></strong></p>
				<p><strong><?php echo __('Data Out').': <span class="data">'.$this->String->size($flows_d_cnt[0][0]['bout']).'</span>';?></strong></p>
				</div>
				<p><strong><?php echo __('Connections from').' <span class="data">'.$this->String->num($ips_cnt).'</span>';?> IP</strong></p>
			</div>
			<div class="floatl">
				<div id="donut_ip2totd" class="donut-chart floatl"></div>
				<div id="donut_ip2datd" class="donut-chart floatl"></div>
			</div>
			<div class="clear">&nbsp;</div>
			<hr>
			<div class="floatl" style="width: 260px; height: 210px;">
				<div class="tabw tbl btbar">
					<table id="ipnamet" cellpadding="0" cellspacing="0" class="nobor txt-cent">
					<thead class="fixed">
						<tr><th><?php echo __('Names');?></th></tr>
					</thead>
					</table>
				</div>
			</div>
			<div class="floatl">
				<div class="centered">
				<p class="donut_cng d inline" data-neme="f"><strong><?php echo __('Flows');?></strong></p>
				<p class="donut_cng d mleft inline" data-neme="t"><strong><?php echo __('Data');?></strong></p>
				<p class="donut_cng d mleft inline" data-neme="i"><strong><?php echo __('Data In');?></strong></p>
				<p class="donut_cng d mleft inline" data-neme="o"><strong><?php echo __('Data Out');?></strong></p>
				</div>
				<div class="clear">&nbsp;</div>
				<div class="floatl">
					<div id="donut_ip2protd" class="donut-chart"></div>
					<p class="centered"><strong><?php echo __('Protocols');?></strong></p>
				</div>
				<div class="floatl">
					<div id="ip2hoursd"></div>
					<p class="centered"><strong><?php echo __('Hours');?></strong></p>
				</div>
			</div>
			<div class="clear">&nbsp;</div>

<script>
	// data
	var datad = {
			f: [{n: '<?php echo __('This IP');?>', val: <?php echo $flows_d_cnt[0][0]['fcnt'];?>}, {n: '<?php echo __('Other');?>', val: <?php echo $flows_cnt[0][0]['fcnt']-$flows_d_cnt[0][0]['fcnt'];?>}],
			i: [{n: '<?php echo __('Data Sent');?>', val: <?php echo $flows_d_cnt[0][0]['bout'];?>}, {n: '<?php echo __('Data Received');?>', val: <?php echo $flows_d_cnt[0][0]['bin'];?>}],
			t: [{n: '<?php echo __('This IP Data');?>', val: <?php echo $flows_d_cnt[0][0]['bout']+$flows_d_cnt[0][0]['bin'];?>}, {n: '<?php echo __('Other IPs');?>', val: <?php echo $flows_cnt[0][0]['bin']+$flows_cnt[0][0]['bout']-$flows_d_cnt[0][0]['bin']-$flows_d_cnt[0][0]['bout'];?>}],
		},
		ip2totd = donut("#donut_ip2totd", datad.f, geom),
		ip2datd = donut("#donut_ip2datd", datad.i, geom1);
	
	$(".donut_cng.c").unbind('mouseover');
	$(".donut_cng.c").mouseover(function() {
		var tp = $(this).attr("data-neme");
		ip2totd.base(basuni[tp].b);
		ip2totd(datad[tp]);
		ip2totd.unit(basuni[tp].u);
	});

	// protocol, country, hours ...
	var datprotd = undefined,
		datcntrd = undefined,
		dathoursd = undefined,
		ip2protd = donut("#donut_ip2protd", datprotd, geom),
		ip2hoursd = chours("#ip2hoursd", dathoursd, geom2);
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdjsn', 'dst', $ip_id, 'sprot.json')); ?>", function(json) {
		datprotd = json;
		ip2protd(datprotd.f);
	});
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdjsn', 'dst', $ip_id, 'hours.json')); ?>", function(json) {
		dathoursd = json;
		ip2hoursd(dathoursd.f);
	});
	$(".donut_cng.d").unbind('mouseover');
	$(".donut_cng.d").mouseover(function() {
		var tp = $(this).attr("data-neme");
		ip2protd.base(basuni[tp].b);
		ip2protd(datprotd[tp]);
		ip2protd.unit(basuni[tp].u);
		ip2hoursd.base(basuni[tp].b);
		ip2hoursd(dathoursd[tp]);
		ip2hoursd.unit(basuni[tp].u);
	});
	// names
	$.ajax({
		url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdjsn', 'dst', $ip_id, 'names.json')); ?>",
	}).done(function(data) {
		var len = data.length;
		for (var i=0; i!=len; i++) {
			if (data[i] != "")
				$('#ipnamet').append('<tr><td>'+data[i]+'</td></tr>');
		}
		if (len > 14)
			$('#ipnamet').parent().addClass("scrolly");
	});
	
</script>
