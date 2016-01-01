<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div class="cdetails mleft mright mtop">
	<div id="ip_dat_min" class="floatl"></div>
	<div id="dat_min" class="floatr" ></div>
</div>
<div class="cdetails mleft mright mtop">
	<div id="ports_map" class="floatl"></div>
	<div id="prots_map" class="floatr" ></div>
</div>
<div class="cdetails mleft mright mtop">
	<div id="prot_cntry" class="floatl"></div>
</div>
<script>
	var margin = {left: 5, right: 5, top: 22, bottom: 20};
		
	/* ip source and data (staring connection reference time) */
	function drawIpsChart(place, data, w, h, label, baserid) {
		d3.selectAll(place+' svg').remove();
		var dx = (w-margin.left-margin.right)/12,
			y = d3.scale.linear()
			.domain([0, d3.max(data, function(d) { d.a = +d.a; d.b = +d.b; return d.a + d.b; })])
			.range([0, h-margin.top-15]);
			
		var svg = d3.select(place)
				.append('svg:svg')
				.attr('class', 'chart')
				.attr('width', w)
				.attr('height', h);
		
		var chart = svg.append('svg:g')
				.attr("transform", "translate("+margin.left+","+ (h-15) +" )scale(1, -1)");
		
		chart.append("line")
			.attr("x2", w-margin.right-margin.left);
			
		var rect = chart.selectAll('rect').data(data),
			text = svg.append('svg:g')
				.attr("transform", "translate("+(margin.left+dx/2)+","+ (h-5) +" )")
				.selectAll('text').data(data),
			label_base = svg.append('svg:g')
				.attr("transform", "translate(0,0)");
				
		label_base.append("text")
			.attr("class", "bold")
			.attr("x", "0")
			.attr("y", ".64em")
			.attr("dy", ".32em")
			.attr("text-anchor", "start")
			.text(label);
			
		rect.enter()
			.append('svg:g')
			.attr("stroke", "white")
			.attr("stroke-width", "1px")
			.on("mouseover", mouseover)
			.on("mouseout", mouseout)
			.append('svg:rect')
				.attr('x', function(d, i) { return i * dx; })
				.attr('y', function(d) { return  0; })
				.attr('height', 0)
				.attr('width', dx)
				.attr('class', 'ra');
		rect.append('svg:rect')
				.attr('x', function(d, i) { return i * dx; })
				.attr('y', 0)
				.attr('height', 0)
				.attr('width', dx)
				.attr('class', 'rb');
				
		text.enter()
			.append('svg:text')
				.attr("x", function(d, i) { return i * dx })
				.attr("y", 0)
				.attr("dy", ".32em")
				.attr("text-anchor", 'middle')
				.text(function(d, i) { return d.min;});
		/* animations */
		chart.selectAll('rect.ra')
			.transition()
			.duration(1000)
			.attr('height', function(d) { return y(d.a); });
			
		chart.selectAll('rect.rb')
			.transition()
			.duration(1000)
			.attr("y", function(d) { return y(d.a); })
			.attr('height', function(d) { return y(d.b); });

		function mouseover(p) {
			d3.select(this).style("opacity", "0.7");
			var nl = ((w-margin.right-margin.left)/2),
				desc = label_base.append('svg:g')
					.attr("transform", "translate("+nl+", 0)")
					.attr('id', 'label_over');
			nl = nl/2;
						
			desc.append("circle")
				.attr("r", 5)
				.attr("cx", 0)
				.attr("cy", 7)
				.attr("stroke", "black")
				.attr("stroke-width", 1.5)
				.attr("class", "ra");
				
			desc.append("text")
				.attr("x", 7)
				.attr("y", ".64em")
				.attr("dy", ".32em")
				.attr("text-anchor", "start")
				.text(SizeBMG(p.a, baserid));
				
			desc.append("circle")
				.attr("r", 5)
				.attr("cx", nl)
				.attr("cy", 7)
				.attr("stroke", "black")
				.attr("stroke-width", 1.5)
				.attr("class", "rb");
				
			desc.append("text")
				.attr("x", nl+7)
				.attr("y", ".64em")
				.attr("dy", ".32em")
				.attr("text-anchor", "start")
				.text(SizeBMG(p.b, baserid));
			
		}
		function mouseout(p) {
			d3.select(this).style("opacity", "");
			label_base.select('#label_over').remove();
		}
	}
	var data = JSON.parse('<?php echo $data; ?>'),
		ipcnt = JSON.parse('<?php echo $ipcnt; ?>'),
		w = $("#ip_dat_min").parent().width()-1;
	drawIpsChart('#dat_min', data, w/2, 130, "<?php echo __('Data direction'); ?>");
	drawIpsChart('#ip_dat_min', ipcnt, w/2, 130, "<?php echo __('IP source counter'); ?>", 1000);

	/* protocols map */
	function drawProtMap(place, data, w, h) {
		var hm = h - margin.top - margin.bottom,
			wm = w - margin.left - margin.right;
		var x = d3.scale.linear().range([0, wm]),
			y = d3.scale.linear().range([0, hm]),
			color = d3.scale.category20c(),
			mlabels = Array({name:'<?php echo __('Flows'); ?>', view: 'cnt'}, {name:'<?php echo __('Data'); ?>', view: 'tot'}, {name:'<?php echo __('Duration'); ?>', view: 'dur'}),
			view = 'cnt',
			root,
			node;

		var treemap = d3.layout.treemap()
			.round(false)
			.size([wm, hm])
			.sticky(true)
			.value(function(d) { return +d[view]; });
				
		var svg = d3.select(place).append("div")
			.attr("class", "chart")
			.style("width", w + "px")
			.style("height", h + "px")
		.append("svg:svg")
			.attr("width", w)
			.attr("height", h);
			
		var labels = svg.append("svg:g")
			.attr("transform", "translate("+margin.left+",0)");
		labels.append("text")
			.attr("text-anchor", "start")
			.attr("class", "bold")
			.attr("x", 0)
			.attr("y", 10)
			.attr("dy", ".32em")
			.text('<?php echo __('Protocols'); ?>:');
		var circles = labels.selectAll("circle")
			.data(mlabels)
			.enter().append("g")
			.attr("transform", function(d, i) { i++; return "translate(" + (i*wm/4) + ", 0)"; })
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
		
		var treem = svg.append("svg:g")
			.attr("transform", "translate("+margin.left+","+margin.top+")");
			
		var infom = svg.append("svg:g")
			.attr("transform", "translate("+margin.left+","+(hm+margin.top)+")");
			
		node = root = {children: data};

		var nodes = treemap.nodes(root);

		var cell = treem.selectAll("g")
				.data(nodes)
			.enter().append("svg:g")
				.attr("class", "cell")
				.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
				.on("mouseover", mouseover)
				.on("mouseout", mouseout);

		cell.append("svg:rect")
			.attr("width", function(d) { return d.dx - 1; })
			.attr("height", function(d) { return d.dy - 1; })
			.style("fill", function(d) { return color(d.l7); });

		cell.append("svg:text")
			.attr("x", function(d) { return d.dx / 2; })
			.attr("y", function(d) { return d.dy / 2; })
			.attr("dy", ".35em")
			.attr("text-anchor", "middle")
			.text(function(d) { return d.l7; })
			.style("opacity", function(d) { d.w = this.getComputedTextLength(); return d.dx > d.w ? 1 : 0; });
			
		function viewchange(p) {
			view = p.view;
			labels.selectAll("circle").style("fill-opacity", "0");
			d3.select(this).select("circle").style("fill-opacity", "1");
			treemap.nodes(root);
			mupdate(node);
		}

		function mupdate(d) {
			var kx = wm / d.dx, ky = hm / d.dy;
			x.domain([d.x, d.x + d.dx]);
			y.domain([d.y, d.y + d.dy]);

			var t = svg.selectAll("g.cell").transition()
				.duration(d3.event.altKey ? 7500 : 750)
				.attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });

			t.select("rect")
				.attr("width", function(d) { return kx * d.dx - 1; })
				.attr("height", function(d) { return ky * d.dy - 1; })

			t.select("text")
				.attr("x", function(d) { return kx * d.dx / 2; })
				.attr("y", function(d) { return ky * d.dy / 2; })
				.style("opacity", function(d) { return kx * d.dx > d.w ? 1 : 0; });

			d3.event.stopPropagation();
		}
		
		function mouseover(p) {
			var mval;
			switch (view) {
			case 'cnt':
				mval = SizeBMG(+p[view], 1000);
				break;
				
			case 'tot':
				mval = SizeBMG(+p[view]);
				mval = mval+"B";
				break;
				
			case 'dur':
				mval = SizeBMG(+p[view], 1000);
				mval = mval+"s";
				break;
			}
				
			d3.select(this).style("opacity", "0.7");
			infom.append('svg:g')
				.attr("transform", "translate(0, 0)")
				.append("text")
				.attr("text-anchor", "start")
				.attr("y", 10)
				.attr("dy", ".32em")
				.text(p.l7+" : "+mval);
		}
		function mouseout(p) {
			d3.select(this).style("opacity", "");
			infom.select('g').remove();
		}
	}
	var prots = JSON.parse('<?php echo $prots; ?>');
	drawProtMap('#prots_map', prots, w/2, 170);

	/* port map */
	var ports = JSON.parse('<?php echo $ports; ?>'),
		mlabels = Array({name:'<?php echo __('IPs'); ?>', view: 'num'}, {name:'<?php echo __('Flows'); ?>', view: 'cnt'}, {name:'<?php echo __('Data'); ?>', view: 'tot'}, {name:'<?php echo __('Duration'); ?>', view: 'dur'}),
		info_labels = {menu: '<?php echo __('dPorts'); ?>', descr: '<?php echo __('Destination Ports');?>'};
	drawPortMap('#ports_map', ports, mlabels, info_labels, w/2, 170, margin);

	/* protocols vs countries */
	var prots = JSON.parse('<?php echo $prot_cntrs; ?>'),
		mclabels = Array({name:'<?php echo __('Flows'); ?>', view: 'cnt'}, {name:'<?php echo __('Data'); ?>', view: 'tot'}),
		cinfo = '<?php echo __('Prtocols vs Countries'); ?>';
	drawProtCountry('#prot_cntry', prots, mclabels, cinfo, w, 20*(prots.nodes.length+2));
</script>

