<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div style="position: absolute; right: 0;">
	<div id="reload_ov" class="treload"><a href="#"><?php echo $this->Html->image('reload.png', array('alt' => '')); ?></a></div>
</div>
<div id="overview">
	<div class="floatl mright mleft">
		<div id="oselect"></div>
		<div id="omat"></div>
	</div>
	<div id="loading">
		<h1 style="font-size:34px;">Loading...</h1>
	</div>
	<div id="details" class="cinfo dispoff">
		<div class="cdetails mleft mright">
			<div class="floatl">
				<h3 id="odate"></h3>
				<h3 id="oflows"></h3>
				<h3 id="odurat"></h3>
				<h3 id="obytes"></h3>
				<h3 id="obyter"></h3>
			</div>
			<div class="floatr">
				<h3>Sent vs Receiv.</h3>
				<div id="opie" class="floatr"></div>
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		<div id="moreinfo"></div>
	</div>
	<div class="clear">&nbsp;</div>
	<script>
	var margin = {top: 30, right: 0, bottom: 10, left: 80},
		width = 300,
		width_label = width + margin.right + margin.left;

	var x = d3.scale.ordinal().rangeBands([0, width]),
		y = d3.scale.ordinal(),
		z = d3.scale.linear().range([0.2, 1]).domain([0, 1]),
		c = d3.scale.ordinal().range(["#5E4FA2", "#3288BD", "#66C2A5", "#ABDDA4", "#E6F598", "#F6FAAA", "#FEE08B", "#FDAE61", "#F46D43", "#D53E4F", "#9E0142"]).domain(d3.range(11));
		cs = d3.scale.linear().rangeRound([0, 10]);

	var matrix = [],
		view = 'num',
		views = [],
		day_name = [],
		max_val = [],
		min_val = [],
		oclick = null;

	d3.select("#details").on("mouseover", viewlastclick);
	var svg = d3.select("#omat")
		.append("svg")
		.attr("class", "matrix")
		.attr("width", width + margin.left + margin.right)
	.append("g")
		.attr("transform", "translate(" + margin.left + "," + margin.top + ")");
	
		function drow(row) {
			rects = row.filter(function(d) {return d[view]; });
			var cell = d3.select(this).selectAll(".cell")
				.data(rects, function(d) { return d.x; });
			cell.exit()
				.transition()
				.delay(0)
				.duration(500)
				.style("fill-opacity", "0")
				.remove();
			
			cell.enter().insert("rect", "line")
				.attr("class", "cell")
				.attr("x", function(d) { return x(d.x); })
				.style("fill-opacity", "0")
				.attr("width", x.rangeBand())
				.attr("height", y.rangeBand()-1)
				.on("mouseover", mouseover)
				.on("mouseout", mouseout)
				.on("click", mouseclick);

			cell.transition()
				.delay(0)
				.duration(1500)
				//.style("fill", function(d, i) { return c(Math.ceil((d[view]/max_val[view])*10)); })
				.style("fill", function(d, i) { return c(cs(d[view])); })
				.style("fill-opacity", "1");
		}

		
	function mouseover(p) {
		d3.select("#details").classed("dispoff", false);
		svg.selectAll(".row text").classed("active", function(d, i) { return i == p.y; });
		svg.selectAll(".column text").classed("active", function(d, i) { return i == p.x; });
		//d3.select(this).style("fill-opacity", "1");
		d3.select("#odate").html("<?php echo __('Day'); ?>: <strong>"+day_name[p.y]+"</strong> <?php echo __('Hour'); ?>: <strong>"+p.x+":00 - "+p.x+":59</strong>");
		d3.select("#oflows").html("<?php echo __('Flows'); ?>:<strong> "+p.num+"</strong>");
		var minutes = Math.floor(p.dur / 60);
		var seconds = Math.floor(p.dur - minutes * 60);
		var hours = Math.floor(minutes / 60);
		minutes = minutes - hours * 60;
		d3.select("#odurat").html("<?php echo __('Duration (avg)'); ?>:<strong> "+hours+":"+minutes+":"+seconds+"</strong> ");
		d3.select("#obytes").html("<?php echo __('Bytes Sent'); ?>:<strong> "+SizeBMG(p.datas)+"</strong>");
		d3.select("#obyter").html("<?php echo __('Bytes Received'); ?>:<strong> "+SizeBMG(p.datar)+"</strong>");
		opie("#opie", [p.datas, p.datar]);
	}

	function mouseout(p) {
		svg.selectAll("text").classed("active", false);
		//d3.select("#value").html("");
	}
		
	function mouseclick(p) {
		// save data
		oclick = p;
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'hour_data')); ?>"+'/'+day_name[p.y]+'/'+p.x+'/'+$("#details").width(),
			context: document.body
		}).done(function(data) { 
			$("#moreinfo").html(data);
		});
	}
	function viewlastclick() {
		if (oclick != null)
			mouseover(oclick);
	}
	function viewchange(p) {
		view = p.view;
		d3.selectAll("#oselect circle").style("fill-opacity", "0");
		d3.select(this).select("circle").style("fill-opacity", "1");
		update();
	}
		
	d3.json("<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'overview', 'overview.json')); ?>", function(hours) {
		var day_key = [],
			n = hours.length,
			day_num = 0,
			k;
		$("#loading").fadeOut(500);
		views[0] = {view:'num', name:'<?php echo __('Flows'); ?>'};
		views[1] = {view:'data', name:'<?php echo __('Data'); ?>'};
		views[2] = {view:'datas', name:'<?php echo __('Sent'); ?>'};
		views[3] = {view:'datar', name:'<?php echo __('Received'); ?>'};
		views[4] = {view:'dur', name:'<?php echo __('Dutation'); ?>'};

		// labels
		ssvg = d3.select("#oselect").append("svg")
			.attr("width", width_label)
			.attr("height", 35);
			
		var circles = ssvg.selectAll("circle")
			.data(views)
			.enter().append("g")
			.attr("transform", function(d, i) { return "translate(" + (i*width_label/(views.length)) + ", 0)"; })
			.on("click", viewchange);
			
		circles.append("circle")
			.attr("r", 5)
			.attr("cx", 10)
			.attr("cy", 10)
			.attr("stroke", "black")
			.attr("stroke-width", 1.5)
			.style("fill-opacity", function(d, i){ if(i!=0) return 0; return 1;})
			.attr("fill", "red");
			
		circles.append("text")
			.attr("text-anchor", "start")
			.attr("x", 18)
			.attr("y", 10)
			.attr("dy", ".32em")
			.text(function(d, i) { return views[i].name; });

		
		var colors = ssvg.append("g")
			.attr("transform", function(d, i) { return "translate(0, 15)"; });
		
		colors.selectAll("rect")
			.data(d3.range(11))
			.enter().append("rect")
			.attr("x", function(d) { return (width_label/11)*d; })
			.attr("y", 7.5)
			.attr("width", width_label/11)
			.attr("height", 3)
			.style("fill", function(d, i) { return c(d); });

		hours.forEach(function(hour, i) {
			hour.days.forEach(function(day, j) {
				var vdur;
				if (!(day.day in day_key)) {
					day_key[day.day] = day_num;
					k = day_num;
					day_num++;
					day_name[k] = day.day;
					matrix[k] = d3.range(n).map(function(h) { return {x: h, y: k}; });
				}
				else {
					k = day_key[day.day];
				}
				vdur = (+day.dur)/(+day.num);
				if (!('num' in max_val)) {
					max_val['num'] = +day.num;
					max_val['datas'] = +day.datas;
					max_val['datar'] = +day.datar;
					max_val['data'] = +day.data;
					max_val['dur'] = vdur;
					min_val['num'] = +day.num;
					min_val['datas'] = +day.datas;
					min_val['datar'] = +day.datar;
					min_val['data'] = +day.data;
					min_val['dur'] = vdur;					
				}
				else {
					if (max_val['num'] < +day.num)
						max_val['num'] = +day.num;
					if (max_val['datas'] < +day.datas)
						max_val['datas'] = +day.datas;
					if (max_val['datar'] < +day.datar)
						max_val['datar'] = +day.datar;
					if (max_val['data'] < +day.data)
						max_val['data'] = +day.data;
					if (max_val['dur'] < vdur)
						max_val['dur'] = vdur;
					if (min_val['num'] > +day.num)
						min_val['num'] = +day.num;
					if (min_val['datas'] > +day.datas)
						min_val['datas'] = +day.datas;
					if (min_val['datar'] > +day.datar)
						min_val['datar'] = +day.datar;
					if (min_val['data'] > +day.data)
						min_val['data'] = +day.data;
					if (min_val['dur'] > vdur)
						min_val['dur'] = vdur;
				}
				matrix[k][i] = {x: i, y: k, num: +day.num, datas: +day.datas, datar: +day.datar, data: +day.data, dur: vdur};
			});
		});
		height = Math.ceil((width/n)*day_num);
		y.rangeBands([0, height]);
		d3.select("#omat svg").attr("height", height + margin.top + margin.bottom);
		
		x.domain(d3.range(n));
		y.domain(d3.range(day_num));
		cs.domain([min_val[view], max_val[view]]);
		
		var name = d3.range(day_num).sort(function(a, b) { return d3.ascending(day_name[a], day_name[b]); });
		
		svg.append("rect")
			.attr("class", "background")
			.attr("width", width)
			.attr("height", height);

		var row = svg.selectAll(".row")
			.data(matrix)
			.enter().append("g")
			.attr("class", "row")
			.attr("transform", function(d, i) { return "translate(0," + y(i) + ")"; })
			.each(drow);

		row.append("line")
			.attr("x2", width);

		row.append("text")
			.attr("x", -6)
			.attr("y", y.rangeBand()/2)
			.attr("dy", ".32em")
			.attr("text-anchor", "end")
			.text(function(d, i) { return day_name[i]; });

		var column = svg.selectAll(".column")
			.data(d3.range(n))
			.enter().append("g")
			.attr("class", "column")
			.attr("transform", function(d, i) { return "translate(" + x(i) + ")rotate(-90)"; });

		column.append("line")
			.attr("x1", -height);

		column.append("text")
			.attr("x", 6)
			.attr("y", x.rangeBand()/2)
			.attr("dy", ".32em")
			.attr("text-anchor", "start")
			.text(function(d, i) { return hours[i].hour; });

		// sort by date
		y.domain(name);
		svg.selectAll(".row")
			.attr("transform", function(d, i) { return "translate(0," + y(i) + ")"; })
	});

	// change
	function update() {
		cs.domain([min_val[view], max_val[view]]);
		svg.selectAll(".row")
			.data(matrix)
			.each(drow);
	}
	
	// reload
	$('#reload_ov').unbind('click');
	$("#reload_ov").click(function() {
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'overview')); ?>",
			context: document.body
		}).done(function(data) { 
			$("#ui-tabs-4").html(data);
		});
	});
	</script>
</div>
<div class="clear">&nbsp;</div>

