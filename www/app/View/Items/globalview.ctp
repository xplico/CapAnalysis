<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div style="position: absolute; right: 0;">
	<div id="reload_glbv" class="treload"><a href="#"><?php echo $this->Html->image('reload.png', array('alt' => '')); ?></a></div>
</div>
<div id="globalview">
	<div class="cdetails mleft mright mtop">
		<div id="gbl_portd" class="floatl"></div>
		<div id="gbl_hours" class="floatr" ></div>
	</div>
	<div class="cdetails mleft mright mtop">
		<div id="gbl_prot_cntr" class="floatl"></div>
		<div id="gbl_prot_day" class="floatl"></div>
	</div>
</div>

<script>
	// port destination
	var margin = {left: 5, right: 5, top: 22, bottom: 20},
		ports = JSON.parse('<?php echo $ports; ?>'),
		w = $("#gbl_portd").parent().width()-1,
		mlabels = Array({name:'<?php echo __('Flows'); ?>', view: 'cnt'}, {name:'<?php echo __('Data'); ?>', view: 'tot'}, {name:'<?php echo __('Duration'); ?>', view: 'dur'}),
		info_labels = {menu: '<?php echo __('dPorts'); ?>', descr: '<?php echo __('Destination Ports');?>'};
		
	drawPortMap("#gbl_portd", ports, mlabels, info_labels, w/3, 200, margin);
	
	function drawHourMap(place, data, w, h, m) {
		d3.selectAll(place+' svg').remove();
		var dx = (w-m.left-m.right)/24,
			rh = h-m.top-m.bottom,
			rw = w-m.right-m.left,
			y = d3.scale.linear()
				.domain([0, d3.max(data, function(d) { d.a = +d.flows; d.b = 0; return d.a; })])
				.range([rh, 0]),
			mlabels = Array({name:'<?php echo __('Flows'); ?>', view: 'flows'}, {name:'<?php echo __('Sent'); ?>', view: 'datas'}, {name:'<?php echo __('Received'); ?>', view: 'datar'}, {name:'<?php echo __('Total Data'); ?>', view: 'tot'}, {name:'<?php echo __('Duration'); ?>', view: 'dur'});

		var yAxis = d3.svg.axis()
				.scale(y)
				.orient("left")
				.tickSize(-w + m.left + m.right)
				.tickFormat(d3.format("s"));
				
		var svg = d3.select(place)
				.append('svg:svg')
				.attr('class', 'chart')
				.attr('width', w)
				.attr('height', h);
		
		var labels = svg.append("svg:g")
				.attr("transform", "translate("+m.left+",0)"),
			chart = svg.append('svg:g')
				.attr("transform", "translate("+m.left+","+ (m.top) +" )");
		
		var circles = labels.selectAll("circle")
				.data(mlabels)
				.enter().append("g")
				.attr("transform", function(d, i) { i++; return "translate(" + (i*rw/6) + ", 0)"; })
				.on("click", viewchange);
		
		circles.append("circle")
			.attr("r", 5)
			.attr("cx", 0)
			.attr("cy", 10)
			.attr("stroke", "black")
			.attr("stroke-width", 1.5)
			.style("fill-opacity", function(d, i){ if(i!=0) return 0; return 1;})
			.attr("fill", "red");
		circles.append("text")
			.attr("text-anchor", "start")
			.attr("x", 8)
			.attr("y", 10)
			.attr("dy", ".32em")
			.text(function(d, i) { return d.name; });
		
		function viewchange(p) {
			labels.selectAll("circle").style("fill-opacity", "0");
			d3.select(this).select("circle").style("fill-opacity", "1");
			switch (p.view) {
				case "flows":
					y.domain([0, d3.max(data, function(d) { d.a = +d.flows; d.b = 0; return d.a; })]);
					break;
				case "tot":
					y.domain([0, d3.max(data, function(d) { d.a = +d.datas; d.b = +d.datar; return d.a + d.b; })]);
					break;
				case "datar":
					y.domain([0, d3.max(data, function(d) { d.a = 0; d.b = +d.datar; return d.b; })]);
					break;
				case "datas":
					y.domain([0, d3.max(data, function(d) { d.a = +d.datas; d.b = 0; return d.a; })]);
					break;
				case "dur":
					y.domain([0, d3.max(data, function(d) { d.a = +d.dur; d.b = 0; return d.a; })]);
					break;
			}
			grphUpdate();
		}
		
		svg.append("svg:g")
			.attr("transform", "translate("+m.left+","+ (m.top) +" )")
			.attr("class", "y axis");
			
		var rect = chart.selectAll('rect').data(data, function(d) {return d.hour;} ),
			text = svg.append('svg:g')
				.attr("transform", "translate("+(m.left+dx/2)+","+ (h-10) +" )")
				.selectAll('text').data(data),
			label_base = svg.append('svg:g')
				.attr("transform", "translate(0,0)");
				
		label_base.append("text")
			.attr("class", "bold")
			.attr("x", 0)
			.attr("y", 10)
			.attr("dy", ".32em")
			.attr("text-anchor", "start")
			.text("<?php echo __('Hours Map'); ?>");
			
		rect.enter()
			.append('svg:g')
			.attr("stroke", "white")
			.attr("stroke-width", "3px")
			.on("mouseover", mouseover)
			.on("mouseout", mouseout)
			.append('svg:rect')
				.attr('x', function(d) { return +d.hour * dx; })
				.attr('y', rh)
				.attr('height', 0)
				.attr('width', dx)
				.attr('class', 'ra');
		rect.append('svg:rect')
				.attr('x', function(d) { return +d.hour * dx; })
				.attr('y', rh)
				.attr('height', 0)
				.attr('width', dx)
				.attr('class', 'rb');
				
		text.enter()
			.append('svg:text')
				.attr("x", function(d) { return +d.hour * dx })
				.attr("y", 0)
				.attr("dy", ".32em")
				.attr("text-anchor", 'middle')
				.text(function(d, i) { return d.hour;});
				
		/* animations */
		function grphUpdate() {
			chart.selectAll('rect.ra')
				.transition()
				.duration(1000)
				.attr("y", function(d) { return y(d.a); })
				.attr('height', function(d) { return rh-y(d.a); });
			
			chart.selectAll('rect.rb')
				.transition()
				.duration(1000)
				.attr("y", function(d) { return y(d.b+d.a); })
				.attr('height', function(d) { return rh-y(d.b); });
					
			svg.select(".y.axis")
				.call(yAxis);
		}
		grphUpdate();
		function mouseover(p) {
			d3.select(this).style("opacity", "0.7");
		}
		function mouseout(p) {
			d3.select(this).style("opacity", "");
		}
	}
	margin.left = 50;
	var hourmap = JSON.parse('<?php echo $hours; ?>');
	drawHourMap("#gbl_hours", hourmap, w*2/3, 200, margin);

	// protocols vs countries
	margin.left = 5;
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'globalview', 'countries.json')); ?>", function(json) {
		mclabels = Array({name:'<?php echo __('Flows'); ?>', view: 'cnt'}, {name:'<?php echo __('Data'); ?>', view: 'tot'}),
		cinfo = '<?php echo __('Prtocols vs Countries'); ?>';
		drawProtCountry('#gbl_prot_cntr', json, mclabels, cinfo, w/2, 15*(json.nodes.length+2));
	});

	// protocols vs days
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'globalview', 'days.json')); ?>", function(json) {
		mclabels = Array({name:'<?php echo __('Flows'); ?>', view: 'cnt'}, {name:'<?php echo __('Data'); ?>', view: 'tot'}),
		cinfo = '<?php echo __('Prtocols vs Days'); ?>';
		drawProtCountry('#gbl_prot_day', json, mclabels, cinfo, w/2, 20*(json.nodes.length+2));
	});

	// reload
	$('#reload_glbv').unbind('click');
	$("#reload_glbv").click(function() {
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'globalview')); ?>",
			context: document.body
		}).done(function(data) { 
			$("#ui-tabs-2").html(data);
		});
	});
</script>
