/*
   CapAnalysis
  
   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
*/

function barStackChart() {
	var margin = {top: 20, right: 30, bottom: 10, left: 50},
		duartion = 500,
		label_h = 10,
		width = 460,
		height = 150,
		sort_bar = true,
		label = null,
		delay = 0,
		values_mame = new Array(),
		y = d3.scale.ordinal(),
		x = d3.scale.linear(),
		//z = d3.scale.ordinal().range(["lightpink", "darkgray", "lightblue"]),
		//z = d3.scale.category20c(),
		z = d3.interpolateRgb("#DF0000", "#0099ff"),
		xAxis = d3.svg.axis()
					.scale(x)
					.orient("top")
					.tickFormat(d3.format("s")),
		yAxis = d3.svg.axis()
					.scale(y)
					.orient("left")
					.tickSize(0);

	function mouseover(d, i) {
		d3.select(this).style("opacity", "0.7");
		label.selectAll("circle").remove();
		label.selectAll("text").remove();
		label.append("circle")
			.attr("r", label_h/2)
			.attr("cx", 13)
			.attr("cy", label_h)
			.attr("stroke", "black")
			.attr("stroke-width", 1.5)
			.attr("fill", z(d.lyr / values_mame.length));
		label.append("text")
			.attr("text-anchor", "start")
			.attr("x", 21)
			.attr("y", label_h)
			.attr("dy", ".32em")
			.text(values_mame[d.lyr]);
	}

	function mouseout(d, i) {
		d3.select(this).style("opacity", "");
		label.selectAll("circle").remove();
		label.selectAll("text").remove();
	}
	

	function redraw(svg, data) {
		var name, layers = 0;
		
		for (key in data[0]) {
			if (layers) {
				values_mame[layers-1] = key;
			}
			else {
				item = key;
			}
			layers++;
		}
		layers--;
		var series = d3.layout.stack()(values_mame.map(function(column) {
			return data.map(function(d) {
				return {x: d[item], y: +d[column], lyr: values_mame.indexOf(column)};
			});
		}));
		y.domain(series[0].map(function(d) { return d.x; }));
		x.domain([0, d3.max(series[series.length - 1], function(d) { return d.y0 + d.y; })]);
		svg.select(".x.axis")
			.call(xAxis);
		svg.select(".y.axis")
			.call(yAxis);

		var items = svg.selectAll("g.item")
			.data(series);
		items.enter().insert("svg:g", ".axis")
			.attr("class", "item")
			.style("fill", function(d, i) { return z(i / layers); })
			.style("stroke", function(d, i) { return d3.rgb(z(i / layers)).darker(); });
			
		var rect = items.selectAll("rect")
			.data(function(d) { return d; }, function(d) { return d.x; });
		rect.exit()
			.transition()
			.delay(delay)
			.duration(duartion)
			.attr("x", 0)
			.attr("width", 0)
			.attr("height", 0)
			.remove();
			
		rect.enter().append("svg:rect")
			.on('mouseover', mouseover)
			.on('mouseout', mouseout)
			.attr("y", function(d) { return y(d.x); })
			.attr("x", 0)
			.attr("width", 0)
			.attr("height", y.rangeBand());
		
		rect.transition()
			.delay(delay)
			.duration(duartion)
			.attr("height", y.rangeBand())
			.attr("width", function(d) { return x(d.y); })
			.attr("y", function(d, i) { return y(d.x);})
			.attr("x", function(d) { return x(d.y0); });
			
		items.exit().remove();
	}

	function chart(selection) {
		var doit = true;
		y.rangeRoundBands([0, height - margin.top - margin.bottom]);
		x.range([0, width - margin.left - margin.right ]);
		xAxis.tickSize(-height+ margin.top + margin.bottom);
		
		selection.each(function(data) {
			// sort
			if (sort_bar && doit) {
				doit = false;
				var values_mame = new Array();
				var layers = 0;
		
				for (key in data[0]) {
					if (layers) {
						values_mame[layers-1] = key;
					}
					layers++;
				}
				layers--;
				data.forEach(function(d) {
					d.bsum = 0;
					for (i=0; i!=layers; i++) {
						d.bsum += (+d[values_mame[i]]);
					}
				});
				data.sort(function(a, b) { return b.bsum - a.bsum; });
				data.forEach(function(d) {
					delete d.bsum;
				});
			}
			// Select the svg element, if it exists.
			var svg = d3.select(this).selectAll("svg").data([data]);
			// Otherwise, create the skeletal chart.
			var gEnter = svg.enter().append("svg")
				.append("svg:g");
			gEnter.append("svg:g")
				.attr("class", "x axis bstk");
			gEnter.append("svg:g")
				.attr("class", "y axis");
			label = svg.append("svg:g")
				.attr("class", "glabel")
				.attr("transform", "translate(0," + (height - margin.bottom)+ ")");
			// Update the outer dimensions.
			svg.attr("width", width)
				.attr("height", height+label_h);
				
			var g = svg.select("g")
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

			redraw(g, data);
		});
		duartion = 2000;
	}
	
	chart.margin = function(_) {
		if (!arguments.length) return margin;
		margin.top = _.top;
		margin.right = _.right;
		margin.bottom = _.bottom;
		margin.left = _.left;
		return chart;
	};

	chart.width = function(_) {
		if (!arguments.length) return width;
		width = _;
		return chart;
	};

	chart.height = function(_) {
		if (!arguments.length) return height;
		height = _;
		return chart;
	};
	
	chart.sort= function(_) {
		if (!arguments.length) return sort_bar;
		sort_bar = _;
		return chart;
	};
	
	chart.delay= function(_) {
		if (!arguments.length) return delay;
		delay = _;
		return chart;
	};
	
	return chart;
}

// pie
function opie(place, data, wp, hp) {
	if (wp == null) {
		wp = 100,
		hp = 70;
	}

	var pie = d3.layout.pie(),
		r = Math.min(wp, hp) / 2 - 4,
		arc = d3.svg.arc().innerRadius(0).outerRadius(r);
	pie.sort(null);
	
	d3.select(place+" svg").remove();
	
	var chart = d3.select(place).append('svg:svg')
		.data([data])
		.attr("width", wp)
		.attr("height", hp);
	
	var arcs = chart.selectAll('g')
		.data(pie)
		.enter().append('svg:g')
			.attr("transform", "translate(" + wp/2 + "," + hp/2 + ")");
	
	arcs.append('svg:path')
		.attr('d', arc)
		.attr('class', function(d, i) { return i === 0 ? 'sent' : 'rec' });
}


function drawPortMap(place, data, mlabels, info, w, h, m) {
	var hm = h - m.top - m.bottom,
		wm = w - m.left - m.right;
	var ang = d3.scale.linear().range([0, 2 * Math.PI]),
		radius_m = Math.min(w - m.right - m.left, h - m.top - m.bottom)/2,
		rd = d3.scale.linear().range([25, radius_m]),
		color = d3.scale.ordinal().range(["#FEE08B", "#FDAE61", "#F46D43", "#D53E4F", "#9E0142"]).domain(d3.range(5)),
		color_map = d3.scale.linear().rangeRound([0, 4]),
		cx = w/2, cy = h/2,
		view = mlabels[0].view;
		
	d3.select(place+" svg").remove();		
	var svg = d3.select(place).append('svg:svg')
			.attr("width", w)
			.attr("height", h),
		labels = svg.append("svg:g")
			.attr("transform", "translate("+m.left+",0)"),
		chart = svg.append("svg:g"),
		infom = svg.append("svg:g")
			.attr("transform", "translate("+m.left+","+(hm+m.top)+")");
	
	labels.append("text")
		.attr("text-anchor", "start")
		.attr("class", "bold")
		.attr("x", 0)
		.attr("y", 10)
		.attr("dy", ".32em")
		.text(info.menu+':');
	var circles = labels.selectAll("circle")
		.data(mlabels)
		.enter().append("g")
		.attr("transform", function(d, i) { i++; return "translate(" + (i*wm/5) + ", 0)"; })
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
		view = p.view;
		labels.selectAll("circle").style("fill-opacity", "0");
		d3.select(this).select("circle").style("fill-opacity", "1");
		chart.selectAll("g").remove();
		initMap();
	}
		
	function mouseover(p) {
		var mval;
		switch (view) {
		case 'num':
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
			.text(info.descr+" ["+p.ports+"-"+p.porte+"] : "+mval);
	}
	
	function mouseout(p) {
		d3.select(this).style("opacity", "");
		infom.select('g').remove();
	}
		
	function initMap() {
		ang.domain([1, d3.max(data, function(d) {return +d.porte})]);
		rd.domain([1, d3.max(data, function(d) {return +d[view]})]);
		color_map.domain([1, d3.max(data, function(d) {return +d[view]})]);

		var arc = d3.svg.arc().innerRadius(0),
			spich = chart.append('svg:g')
				.attr("transform", "translate(" + cx + "," + cy + ")");
			spich.selectAll('path')
				.data(data)
				.enter().append('svg:path')
				.attr("class", "assim_arc")
				.attr('d', function(d) {var r = d[view]? rd(d[view]): 0; return arc({outerRadius: r, startAngle: Math.PI/2-ang(d.ports), endAngle: Math.PI/2-ang(d.porte)});})
				.attr("fill", function(d) {return color(color_map(d[view]));})
				.on("mouseover", mouseover)
				.on("mouseout", mouseout);
			
		var ticks = rd.ticks(5),
			grid_c = chart.append('svg:g'),
			grid_r = chart.append('svg:g');
		grid_c.selectAll('circle')
			.data(ticks)
			.enter().append('circle')
			.attr("r", function(d) {return rd(d);})
			.attr("cx", cx)
			.attr("cy", cy)
			.attr("stroke", "#aaaaaa")
			.attr("stroke-width", 1)
			.attr("fill", "none");
		grid_c.selectAll('text')
			.data(ticks)
			.enter().append('text')
			.attr("text-anchor", "middle")
			.attr("x", cx)
			.attr("y", function(d, i) {return (i%2) ? rd(d)+cy: cy-rd(d); })
			.attr("dy", function(d, i) {return (i%2) ? ".96em": "";})
			.text(function(d, i) { return SizeBMG(d, 1000); });
				
		ticks = ang.ticks(5);
		grid_r.selectAll('line')
			.data(ticks)
			.enter().append('line')
			.attr("x1", cx)
			.attr("y1", cy)
			.attr("x2", function(d,i) {return cx+Math.cos(ang(d))*radius_m; })
			.attr("y2", function(d,i) {return cy-Math.sin(ang(d))*radius_m; });
		grid_r.selectAll('text')
			.data(ticks)
			.enter().append('text')
			.attr("text-anchor", function(d,i) {return (ang(d)>Math.PI/2 && ang(d)<3/2*Math.PI) ? "end" : "start";})
			.attr("x", function(d,i) {return cx+Math.cos(ang(d))*radius_m; })
			.attr("y", function(d,i) {return cy-Math.sin(ang(d))*radius_m; })
			.attr("dy", ".32em")
			.text(function(d, i) { return SizeBMG(d, 1000); });
	}
	initMap();
}


function drawProtCountry(place, data, mlabels, info, w, h) {
	var width = w - margin.left - margin.right,
		height = h - margin.top - margin.bottom;

	var formatNumber = d3.format(",.0f"),
		format = function(d) { return formatNumber(d); },
		color = d3.scale.category20();

	var svg = d3.select(place).append("svg")
			.attr("class", "sankey")
			.attr("width", w)
			.attr("height", h),
		chart = svg.append("g")
			.attr("transform", "translate(" + margin.left + "," + margin.top + ")"),
		labels = svg.append("svg:g")
			.attr("transform", "translate("+margin.left+",0)");
	/* labes */
	labels.append("text")
		.attr("text-anchor", "start")
		.attr("class", "bold")
		.attr("x", 0)
		.attr("y", 10)
		.attr("dy", ".32em")
		.text(info+':');
	var circles = labels.selectAll("circle")
		.data(mlabels)
		.enter().append("g")
		.attr("transform", function(d, i) { i++; return "translate(" + (i*width/3) + ", 0)"; })
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
		if (p.view == 'cnt')
			updateSankey(data.links);
		else
			updateSankey(data.data);
	}
	/* sankey */
	var sankey = d3.sankey()
		.nodeWidth(8)
		.nodePadding(10)
		.size([width, height]);

	var path = sankey.link();
	
	function updateSankey(linkset) {
		var duration = 900;
		
		chart.selectAll(".link")
			.transition()
			.duration(duration)
			.style("stroke-width", 0)
			.remove();
		sankey
			.nodes(data.nodes)
			.links(linkset)
			.layout(62);

		chart.selectAll(".node")
			.transition()
			.delay(duration)
			.duration(duration)
			.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
			
		chart.selectAll(".node")
			.call(d3.behavior.drag()
			.origin(function(d) { return d; })
			.on("dragstart", function() { this.parentNode.appendChild(this); })
			.on("drag", dragmove));
		
		chart.selectAll(".node rect")
			.transition()
			.delay(duration)
			.duration(duration)
			.attr("height", function(d) { return d.dy; })

		chart.selectAll(".node text")
			.transition()
			.delay(duration)
			.duration(duration)
			.attr("y", function(d) { return d.dy / 2; })
			.filter(function(d) { return d.x < width / 2; })

		chart.selectAll(".node text")
			.transition()
			.delay(duration*3)
			.duration(0)
			.text(function(d) { return d.dy ? CountryName(d.name) : ""; })

		var link = chart.append("g").selectAll(".link")
			.data(linkset)
		.enter().append("path")
			.attr("class", function(d) {return (typeof d.type === 'undefined') ? "link": "link "+d.type;})
			.attr("d", path)
			.style("stroke-width", 0)
			.sort(function(a, b) { return b.dy - a.dy; });

		link.transition()
			.delay(duration*2)
			.duration(duration)
			.style("stroke-width", function(d) { return Math.max(1, d.dy); })
		
		link.append("title")
			.text(function(d) { return d.source.name + " : " + CountryName(d.target.name) + " " + format(d.value); });

		function dragmove(d) {
			d3.select(this).attr("transform", "translate(" + d.x + "," + (d.y = Math.max(0, Math.min(height - d.dy, d3.event.y))) + ")");
			sankey.relayout();
			link.attr("d", path);
		}
	}

	function drawSankey(linkset) {
		sankey.nodes(data.nodes)
			.links(linkset)
			.layout(62);

		var link = chart.append("g").selectAll(".link")
			.data(linkset)
		.enter().append("path")
			.attr("class", function(d) {return (typeof d.type === 'undefined') ? "link": "link "+d.type;})
			.attr("d", path)
			.style("stroke-width", function(d) { return Math.max(1, d.dy); })
			.sort(function(a, b) { return b.dy - a.dy; });

		link.append("title")
			.text(function(d) { return d.source.name + " : " + CountryName(d.target.name) + " " + format(d.value); });
			
		var node = chart.append("g").selectAll(".node")
			.data(data.nodes)
		.enter().append("g")
			.attr("class", "node")
			.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
		.call(d3.behavior.drag()
			.origin(function(d) { return d; })
			.on("dragstart", function() { this.parentNode.appendChild(this); })
			.on("drag", dragmove));

		node.append("rect")
			.attr("height", function(d) { return d.dy; })
			.attr("width", sankey.nodeWidth())
			.style("fill", function(d) { return d.color = color(d.name.replace(/ .*/, "")); })
			.style("stroke", function(d) { return d3.rgb(d.color).darker(2); })
		.append("title")
			.text(function(d) { return CountryName(d.name) + " " + format(d.value); });
	
		node.append("text")
			.attr("x", -6)
			.attr("y", function(d) { return d.dy / 2; })
			.attr("dy", ".35em")
			.attr("text-anchor", "end")
			.attr("transform", null)
			.text(function(d) { return d.dy ? CountryName(d.name) : ""; })
			.filter(function(d) { return d.x < width / 2; })
			.attr("x", 6 + sankey.nodeWidth())
			.attr("text-anchor", "start");

		function dragmove(d) {
			d3.select(this).attr("transform", "translate(" + d.x + "," + (d.y = Math.max(0, Math.min(height - d.dy, d3.event.y))) + ")");
			sankey.relayout();
			link.attr("d", path);
		}
	}
	drawSankey(data.links);
}

function donut(place, data, geom) {
	var w = geom.w;
	var h = geom.h;
	var r = geom.r;
	var ir = geom.ir;
	var base = geom.base;
	var textOffset = 14;
	var tweenDuration = 500;

	//OBJECTS TO BE POPULATED WITH DATA LATER
	var lines, valueLabels, nameLabels;
	var pieData = [];    
	var oldPieData = [];
	var filteredPieData = [];

	//D3 helper function to populate pie slice parameters from array data
	var donutf = d3.layout.pie().value(function(d){
		return +d.val;
	});

	//D3 helper function to create colors from an ordinal scale
	var color = d3.scale.category20();

	//D3 helper function to draw arcs, populates parameter "d" in path object
	var arc = d3.svg.arc()
		.startAngle(function(d){ return d.startAngle; })
		.endAngle(function(d){ return d.endAngle; })
		.innerRadius(ir)
		.outerRadius(r);

	// CREATE VIS & GROUPS 
	var vis = d3.select(place).append("svg:svg")
		.attr("width", w)
		.attr("height", h);

	//GROUP FOR ARCS/PATHS
	var arc_group = vis.append("svg:g")
		.attr("class", "arc")
		.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

	//GROUP FOR LABELS
	var label_group = vis.append("svg:g")
		.attr("class", "label_group")
		.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

	//GROUP FOR CENTER TEXT  
	var center_group = vis.append("svg:g")
		.attr("class", "center_group")
		.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");

	//PLACEHOLDER GRAY CIRCLE
	var paths = arc_group.append("svg:circle")
		.attr("fill", "#EFEFEF")
		.attr("r", r);

	// CENTER TEXT

	//WHITE CIRCLE BEHIND LABELS
	var whiteCircle = center_group.append("svg:circle")
		.attr("fill", "white")
		.attr("r", ir);

	// "TOTAL" LABEL
	var totalLabel = center_group.append("svg:text")
		.attr("class", "label")
		.attr("dy", -15)
		.attr("text-anchor", "middle") // text-align: right
		.text("TOTAL");

	//TOTAL VALUE
	var totalValue = center_group.append("svg:text")
		.attr("class", "total")
		.attr("dy", 7)
		.attr("text-anchor", "middle") // text-align: right
		.text("0");

	//UNITS LABEL
	var totalUnits = center_group.append("svg:text")
		.attr("class", "units")
		.attr("dy", 21)
		.attr("text-anchor", "middle") // text-align: right
		.text(geom.unit);
		
	if (typeof(data) != "undefined")
		update(data);
	
	function update(data) {
		oldPieData = filteredPieData;
		pieData = donutf(data);
  
		var totalOctets = 0;
		filteredPieData = pieData.filter(filterData);
		if (oldPieData.length == 0) {
			oldPieData = filteredPieData;
		}
 
		function filterData(element, index, array) {
			element.name = data[index].n;
			element.value = +data[index].val;
			totalOctets += element.value;
			return (element.value > 0);
		}

		if (totalOctets) {
			//REMOVE PLACEHOLDER CIRCLE
			arc_group.selectAll("circle").remove();

			totalValue.text(function(){
				return SizeBMG(totalOctets, base);
			});

			//DRAW ARC PATHS
			paths = arc_group.selectAll("path").data(filteredPieData);
			paths.enter().append("svg:path")
				.attr("stroke", "white")
				.attr("stroke-width", 0.5)
				.attr("fill", function(d, i) { return color(i); })
				.transition()
				.duration(tweenDuration)
				.attrTween("d", pieTween);
			paths
				.transition()
				.duration(tweenDuration)
				.attrTween("d", pieTween);
			paths.exit()
				.transition()
				.duration(tweenDuration)
				.attrTween("d", removePieTween)
				.remove();

			//DRAW TICK MARK LINES FOR LABELS
			lines = label_group.selectAll("line").data(filteredPieData);
			lines.enter().append("svg:line")
				.attr("x1", 0)
				.attr("x2", 0)
				.attr("y1", -r-3)
				.attr("y2", -r-8)
				.attr("stroke", "gray")
				.attr("transform", function(d) {
					return "rotate(" + (d.startAngle+d.endAngle)/2 * (180/Math.PI) + ")";
				});
			lines.transition()
				.duration(tweenDuration)
				.attr("transform", function(d) {
					return "rotate(" + (d.startAngle+d.endAngle)/2 * (180/Math.PI) + ")";
				});
			lines.exit().remove();

			//DRAW LABELS WITH PERCENTAGE VALUES
			valueLabels = label_group.selectAll("text.value").data(filteredPieData)
				.attr("dy", function(d){
					if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
						return 5;
					} else {
						return -7;
					}
				})
				.attr("text-anchor", function(d){
					if ( (d.startAngle+d.endAngle)/2 < Math.PI ){
						return "beginning";
					} else {
						return "end";
					}
				})
				.text(function(d){
					var percentage = (d.value/totalOctets)*100;
					if (percentage > 3 || filteredPieData.length < 15)
						return percentage.toFixed(1) + "%";
					return '';
				});

			valueLabels.enter().append("svg:text")
				.attr("class", "value")
				.attr("transform", function(d) {
					return "translate(" + Math.cos(((d.startAngle+d.endAngle - Math.PI)/2)) * (r+textOffset) + "," + Math.sin((d.startAngle+d.endAngle - Math.PI)/2) * (r+textOffset) + ")";
				})
				.attr("dy", function(d){
					if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
						return 5;
					} else {
						return -7;
					}
				})
				.attr("text-anchor", function(d){
					if ( (d.startAngle+d.endAngle)/2 < Math.PI ){
						return "beginning";
					} else {
						return "end";
					}
				}).text(function(d){
					var percentage = (d.value/totalOctets)*100;
					if (percentage > 3 || filteredPieData.length < 15)
						return percentage.toFixed(1) + "%";
					return '';
				});

			valueLabels.transition().duration(tweenDuration).attrTween("transform", textTween);

			valueLabels.exit().remove();


			//DRAW LABELS WITH ENTITY NAMES
			nameLabels = label_group.selectAll("text.units").data(filteredPieData)
				.attr("dy", function(d){
					if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
						return 17;
					} else {
						return 5;
					}
				})
				.attr("text-anchor", function(d){
					if ((d.startAngle+d.endAngle)/2 < Math.PI ) {
						return "beginning";
					} else {
						return "end";
					}
				}).text(function(d){
					var percentage = (d.value/totalOctets)*100;
					if (percentage > 3 || filteredPieData.length < 15)
						return d.name;
					return '';
				});

			nameLabels.enter().append("svg:text")
				.attr("class", "units")
				.attr("transform", function(d) {
					return "translate(" + Math.cos(((d.startAngle+d.endAngle - Math.PI)/2)) * (r+textOffset) + "," + Math.sin((d.startAngle+d.endAngle - Math.PI)/2) * (r+textOffset) + ")";
				})
				.attr("dy", function(d){
					if ((d.startAngle+d.endAngle)/2 > Math.PI/2 && (d.startAngle+d.endAngle)/2 < Math.PI*1.5 ) {
						return 17;
					} else {
						return 5;
					}
				})
				.attr("text-anchor", function(d){
					if ((d.startAngle+d.endAngle)/2 < Math.PI ) {
						return "beginning";
					} else {
						return "end";
					}
				}).text(function(d){
					var percentage = (d.value/totalOctets)*100;
					if (percentage > 3 || filteredPieData.length < 15)
						return d.name;
					return '';
				});

			nameLabels.transition().duration(tweenDuration).attrTween("transform", textTween);

			nameLabels.exit().remove();
		}
		else {
			arc_group.append("svg:circle")
				.attr("fill", "#EFEFEF")
				.attr("r", r);
			totalValue.text(0);
			if (typeof(lines) != "undefined") {
				lines.remove();
				nameLabels.remove();
				valueLabels.remove();
			}
		}
	}
	
	update.base = function(_) {
		base = _;
		return update;
	};
	
	update.unit = function(_) {
		totalUnits.text(_);
		return update;
	};
	
	function pieTween(d, i) {
		var s0;
		var e0;
		
		if (oldPieData[i]){
			s0 = oldPieData[i].startAngle;
			e0 = oldPieData[i].endAngle;
		} else if (!(oldPieData[i]) && oldPieData[i-1]) {
			s0 = oldPieData[i-1].endAngle;
			e0 = oldPieData[i-1].endAngle;
		} else if (!(oldPieData[i-1]) && oldPieData.length > 0){
			s0 = oldPieData[oldPieData.length-1].endAngle;
			e0 = oldPieData[oldPieData.length-1].endAngle;
		} else {
			s0 = 0;
			e0 = 0;
		}
		var i = d3.interpolate({startAngle: s0, endAngle: e0}, {startAngle: d.startAngle, endAngle: d.endAngle});
		return function(t) {
			var b = i(t);
			return arc(b);
		};
	}

	function removePieTween(d, i) {
		s0 = 2 * Math.PI;
		e0 = 2 * Math.PI;
		var i = d3.interpolate({startAngle: d.startAngle, endAngle: d.endAngle}, {startAngle: s0, endAngle: e0});
		return function(t) {
			var b = i(t);
			return arc(b);
		};
	}

	function textTween(d, i) {
		var a;
		if (oldPieData[i]){
			a = (oldPieData[i].startAngle + oldPieData[i].endAngle - Math.PI)/2;
		} else if (!(oldPieData[i]) && oldPieData[i-1]) {
			a = (oldPieData[i-1].startAngle + oldPieData[i-1].endAngle - Math.PI)/2;
		} else if(!(oldPieData[i-1]) && oldPieData.length > 0) {
			a = (oldPieData[oldPieData.length-1].startAngle + oldPieData[oldPieData.length-1].endAngle - Math.PI)/2;
		} else {
			a = 0;
		}
		var b = (d.startAngle + d.endAngle - Math.PI)/2;

		var fn = d3.interpolateNumber(a, b);
			return function(t) {
			var val = fn(t);
			return "translate(" + Math.cos(val) * (r+textOffset) + "," + Math.sin(val) * (r+textOffset) + ")";
		};
	}
	
	return update;
}

function chours(place, data, geom) {
	var h = geom.h,
		w = geom.w;
	var ang = d3.scale.linear().range([Math.PI/2, -3/2*Math.PI]).domain([0, 24]),
		radius_m = geom.r,
		label_r = (geom.r + Math.min(h, w)/2)/2,
		rd = d3.scale.linear().range([geom.ir, radius_m]),
		color = d3.scale.ordinal().range(["#5E4FA2", "#3288BD", "#66C2A5", "#ABDDA4", "#E6F598", "#F6FAAA", "#FEE08B", "#FDAE61", "#F46D43", "#D53E4F", "#9E0142"]).domain(d3.range(11));
		color_map = d3.scale.linear().rangeRound([0, 10]),
		cx = w/2, cy = h/2;
		
	d3.select(place+" svg").remove();		
	var svg = d3.select(place).append('svg:svg')
			.attr("class", "chours")
			.attr("width", w)
			.attr("height", h),
		chart = svg.append("svg:g");
		
	var center_group = svg.append("svg:g")
		.attr("class", "center_group")
		.attr("transform", "translate(" + (w/2) + "," + (h/2) + ")");
	// circle
	center_group.append("svg:circle")
		.attr("fill", "white")
		.attr("r", geom.ir);
	// "Main" label
	var totalLabel = center_group.append("svg:text")
		.attr("class", "label")
		.attr("dy", -15)
		.attr("text-anchor", "middle") // text-align: right
		.text("");
	//Main value
	var totalValue = center_group.append("svg:text")
		.attr("class", "total")
		.attr("dy", 7)
		.attr("text-anchor", "middle") // text-align: right
		.text("");
	//Main label
	var totalUnits = center_group.append("svg:text")
		.attr("class", "units")
		.attr("dy", 21)
		.attr("text-anchor", "middle") // text-align: right
		.text(geom.unit);
		
	// spicchi d'arco
	var arc = d3.svg.arc().innerRadius(0),
		spich = chart.append('svg:g')
			.attr("transform", "translate(" + cx + "," + cy + ")");
	// axis
	var grid_c = chart.append('svg:g').attr("class", "ax"),
		grid_r = chart.append('svg:g').attr("class", "ax");
	
	if (typeof(data) != "undefined")
		update(data);
		
	function update(data) {
		rd.domain([0, d3.max(data, function(d) {return +d.y})]);
		color_map.domain([d3.min(data, function(d) {return +d.y}), d3.max(data, function(d) {return +d.y})]);

		var vis = spich.selectAll('path').data(data);
		vis.enter().append('svg:path');
		vis.attr('d', function(d) {return arc({outerRadius: rd(d.y), startAngle: Math.PI/2-ang(+d.x), endAngle: Math.PI/2-ang(+d.x+1)});})
			.attr("fill", function(d) {return color(color_map(d.y));});
		vis.exit().remove();
		
		var ticks = rd.ticks(5),
		vis = grid_c.selectAll('circle').data(ticks);
		vis.enter().append('circle')
			.attr("fill", "none")
			.attr("cx", cx)
			.attr("cy", cy);	
		vis.attr("r", function(d) {return rd(d);})
		vis.exit().remove();
		
		vis = grid_c.selectAll('text').data(ticks);
		vis.enter().append('text')
			.attr("text-anchor", "middle")
			.attr("x", cx);
		vis.attr("y", function(d, i) {return (i%2) ? rd(d)+cy: cy-rd(d); })
			.attr("dy", function(d, i) {return (i%2) ? ".96em": "";})
			.text(function(d, i) { return SizeBMG(d, 1000); });
		vis.exit().remove();
				
		ticks = d3.range(24),
		vis = grid_r.selectAll('line').data(ticks);
		vis.enter().append('line')
			.attr("x1", cx)
			.attr("y1", cy);
		vis.attr("x2", function(d,i) {return cx+Math.cos(ang(d))*label_r*0.9; })
			.attr("y2", function(d,i) {return cy-Math.sin(ang(d))*label_r*0.9; });
		vis.exit().remove();
		
		vis = grid_r.selectAll('text').data(ticks);
		vis.enter().append('text')
			.attr("text-anchor", "middle")
			.attr("dy", ".32em");
		vis.attr("x", function(d,i) {return cx+Math.cos(ang(d+0.5))*label_r; })
			.attr("y", function(d,i) {return cy-Math.sin(ang(d+0.5))*label_r; })
			.text(function(d, i) { return SizeBMG(d, 1000); });
		vis.exit().remove();
	}
	
	update.base = function(_) {
		base = _;
		return update;
	};
	
	update.unit = function(_) {
		totalUnits.text(_);
		return update;
	};
	
	return update;
}


function gtree(place, data, geom) {
	var root = data,
		i = 0,
		tree = d3.layout.tree()
			.size([geom.h, geom.w-geom.mr-geom.ml])
		diagonal = d3.svg.diagonal()
			.projection(function(d) { return [d.y, d.x]; }),
		svg = d3.select(place).append("svg:svg")
			.attr("width", geom.w)
			.attr("height", geom.h+geom.mt+geom.mb),
		vis = svg.append("svg:g")
			.attr("transform", "translate(" + geom.ml + "," + geom.mt + ")");
			
		tree.sort(function(a, b) {
			a.r = +a.r;
			a.s = +a.s;
			b.r = +b.r;
			b.s = +b.s;
			return (b.r+b.s) - (a.r+a.s);
		});
		
		root.x0 = (geom.h-geom.mt-geom.mb) / 2;
		root.y0 = 0;

		// toogle all node
		function toggleAll(d) {
			if (d.children) {
				d.children.forEach(toggleAll);
				toggle(d);
			}
		}
		// Toggle children
		function toggle(d) {
			if (d.children) {
				d._children = d.children;
				d.children = null;
			} else {
				d.children = d._children;
				d._children = null;
			}
		}
		// Initialize the display to show a few nodes
		root.children.forEach(toggleAll);

		update(root);
	
		function update(source) {
			var duration = d3.event && d3.event.altKey ? 5000 : 500;

			// Compute the new tree layout.
			var nodes = tree.nodes(root);

			// image size
			var h = geom.h*(nodes.length);
			if (h < 100) h = 100;
			svg.attr("height", h+geom.mt+geom.mb)
			tree.size([h, geom.w-geom.mr-geom.ml]);
			root.x0 = (h/2);
			var nodes = tree.nodes(root).reverse();
			
			// Normalize for fixed-depth.
			nodes.forEach(function(d) { d.y = d.depth * geom.w/3; });

			// Update the nodes…
			var node = vis.selectAll("g.node")
				.data(nodes, function(d) { return d.id || (d.id = ++i); });

			// Enter any new nodes at the parent's previous position.
			var nodeEnter = node.enter().append("svg:g")
					.attr("class", "node")
					.attr("transform", function(d) { return "translate(" + source.y0 + "," + source.x0 + ")"; })
				.on("click", function(d) {
						toggle(d);
						update(d);
				});

			nodeEnter.append("svg:circle")
				.attr("r", 1e-6)
				.style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

			nodeEnter.append("svg:text")
				.attr("x", function(d) { return d.children || d._children ? -10 : 45; })
				.attr("dy", ".35em")
				.attr("text-anchor", function(d) { return d.children || d._children ? "end" : "start"; })
				.text(function(d) { return CountryName(d.name); })
				.style("fill-opacity", 1e-6);

			// info chart
			var info = nodeEnter.append("svg:g")
				.style("opacity", 1);
				
			// * info flow
			info.append("svg:rect")
				.attr("class", "info")
				.attr("y", -7)
				.attr("x", 10)
				.attr("width", 30)
				.attr("height", 14);
			info.append("svg:rect")
				.attr("class", "flow")
				.attr("y", -2)
				.attr("x", 11)
				.attr("width", function(d) {return source ? 28*(+d.f)/(+source.f) : 28;})
				.attr("height", 4);
			info.append("svg:rect")
				.attr("class", "data")
				.attr("y", -6)
				.attr("x", 11)
				.attr("width", function(d) {return (source && (+d.r)+(+d.s))? 28*((+d.r)+(+d.s))/((+source.r)+(+source.s)) : 0;})
				.attr("height", 4);
			info.append("svg:rect")
				.attr("class", "rec")
				.attr("y", 2)
				.attr("x", 11)
				.attr("width", function(d) {return +d.r? 28*(+d.r)/((+d.r)+(+d.s)) : 0;})
				.attr("height", 4);
			info.append("svg:rect")
				.attr("class", "sent")
				.attr("y", 2)
				.attr("x", function(d) {return  +d.r? 11+28*(+d.r)/((+d.r)+(+d.s)) : 0;})
				.attr("width", function(d) {return  +d.r? 28*(+d.s)/((+d.r)+(+d.s)) : 0;})
				.attr("height", 4);
			info.filter(function(d) {return d.children || d._children ? 1: 0;})
				.append("svg:text")
				.attr("x", 47)
				.attr("dy", ".35em")
				.attr("text-anchor", "start")
				.text(function(d) { return d.ip; });
			
			

			// Transition nodes to their new position.
			var nodeUpdate = node.transition()
				.duration(duration)
				.attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; });

			nodeUpdate.select("circle")
				.attr("r", 4.5)
				.style("fill", function(d) { return d._children ? "lightsteelblue" : "#fff"; });

			nodeUpdate.select("text")
				.style("fill-opacity", 1);

			nodeUpdate.select("g")
				.style("opacity", function(d) { return !d.children || d._children ? 1 : 0; });
			
			// Transition exiting nodes to the parent's new position.
			var nodeExit = node.exit().transition()
				.duration(duration)
				.attr("transform", function(d) { return "translate(" + source.y + "," + source.x + ")"; })
				.remove();

			nodeExit.select("circle")
				.attr("r", 1e-6);

			nodeExit.select("text")
				.style("fill-opacity", 1e-6);

			// Update the links…
			var link = vis.selectAll("path.link")
				.data(tree.links(nodes), function(d) { return d.target.id; });

			// Enter any new links at the parent's previous position.
			link.enter().insert("svg:path", "g")
				.attr("class", "link")
				.attr("d", function(d) {
					var o = {x: source.x0, y: source.y0};
					return diagonal({source: o, target: o});
				})
				.transition()
				.duration(duration)
				.attr("d", diagonal);

			// Transition links to their new position.
			link.transition()
				.duration(duration)
				.attr("d", diagonal);
		
			// Transition exiting nodes to the parent's new position.
			link.exit().transition()
				.duration(duration)
				.attr("d", function(d) {
					var o = {x: source.x, y: source.y};
					return diagonal({source: o, target: o});
				})
				.remove();

			// Stash the old positions for transition.
			nodes.forEach(function(d) {
				d.x0 = d.x;
				d.y0 = d.y;
			});
		}

		return update;
}

function timeline(place, data, xnum, geom) {
	var dx = (geom.w-geom.ml-geom.mr)/xnum,
		rh = geom.h-geom.mt-geom.mb,
		rw = geom.w-geom.ml-geom.mr,
		lx = 0,
		len = true,
		legtxt = "---",
		y = d3.scale.linear()
			.domain([0, d3.max(data, function(d) {return +d.y;})])
			.range([rh, 0]);
			
	if (data.length)
		legtxt = data[0].tm+' - '+data[data.length-1].tm;
	
	var yAxis = d3.svg.axis()
			.scale(y)
			.orient("left")
			.ticks(7)
			.tickSize(-geom.w + geom.ml + geom.mr)
			.tickFormat(d3.format("s"));
	
	var legend = d3.select(place)
			.append('div')
			.attr("class", "abslegend"),
		ltext = legend.append('div')
			.attr("class", "ltext floatl")
			.text(legtxt),
		lvalue = legend.append('div')
			.attr("class", "lvalue floatl dispoff");
			
	var svg = d3.select(place)
			.append('svg:svg')
			.on('click', mouseclick)
			.attr('class', 'chart')
			.attr('width', geom.w)
			.attr('height', geom.h),
		chart = svg.append('svg:g')
			.attr("transform", "translate("+geom.ml+","+ (geom.mt) +" )");
	
	svg.append("svg:g")
		.attr("transform", "translate("+geom.ml+","+ (geom.mt) +" )")
		.attr("class", "y axis");
			
	var rectg = chart.selectAll('rect').data(data);		
	/*
	var line = svg.append('svg:line')
			.attr('x1', 0)
			.attr('y1', 0)
			.attr('x2', 0)
			.attr('y2', geom.h-geom.mb+3)
			.attr('class', 'selection_line');
		
	svg.on('mousemove', function () {
		lx = d3.mouse(this)[0];
		if (len) {
			linedraw();
		}
		len = false;
	});
    
	var linedraw = function () {
		var olx = lx;
		line.transition()
			.duration(30)
			.attrTween('transform', d3.tween('translate(' + lx + ', 0)', d3.interpolateString))
			.each('end', function () {if(olx != lx) linedraw(); else len = true;});
	};
    */	
	var rect = rectg.enter().append('svg:g');
	rect.append('svg:rect')
		.attr('x', function(d) { return +d.x * dx; })
		.attr('y', rh)
		.attr('height', 0)
		.attr('width', dx)
		.attr('class', geom.c+" smlstr");
	rect.append('svg:rect')
		.attr('class', "invisible")
		.on('mouseover', mouseover)
		.attr('x', function(d) { return +d.x * dx; })
		.attr('y', 0)
		.attr('height', rh)
		.attr('width', function() {return dx>2 ? dx : 2;});
	
	var pointer = chart.append('svg:circle')
			.attr('class', 'pointer')
			.attr('cx', 0)
			.attr('cy', -3)
			.attr('r', 2);
			
	if (typeof(data) != "undefined")
		update(data);
		
	/* mouse */
	function mouseover(p) {
		ltext.text(p.tm);
		lvalue.text(SizeBMG(p.y))
			.classed("dispoff", false);
		pointer.transition()
			.duration(100)
			.attrTween('transform', d3.tween('translate(' + (+p.x+0.5)*dx+ ',' + y(+p.y) + ')', d3.interpolateString));
	}
	function mouseclick(p) {
		ltext.text(legtxt);
		lvalue.classed("dispoff", true);
		pointer.transition()
			.duration(100)
			.attrTween('transform', d3.tween('translate(0,'+rh+')', d3.interpolateString));
	}
		
	/* update */
	function update(data) {
		y = d3.scale.linear()
			.domain([0, d3.max(data, function(d) {return +d.y;})])
			.range([rh, 0]);
			
		chart.selectAll('rect.'+geom.c).data(data)
			.transition()
			.duration(1000)
			.attr("y", function(d) { return y(+d.y); })
			.attr('height', function(d) { return rh-y(+d.y); });
		
		svg.select(".y.axis")
			.call(yAxis);
	}
		
	update.base = function(_) {
		base = _;
		return update;
	};

	return update;
}


function protocolC(place, dat, m) {
	var xyfun = [function(d) { return +d.ipd; }, function(d) { return +d.ips; }, function(d) { return +d.i+(+d.o); }, function(d) { return +d.f; }],
		xylabels = ["IP Destination", "IP Source", "Data", "Flows"],
		x = xyfun[0],
		y = xyfun[1];
	var rfun = [function(d) { return +d.ipd; }, function(d) { return +d.ips; }, function(d) { return +d.i+(+d.o); }, function(d) { return +d.f; }, function(d) { return +d.i; }, function(d) { return +d.o; }, function(d) { return ((+d.o)+(+d.i))/(+d.f); }],
		rlabels = ["IP Destination", "IP Source", "Data", "Flows", "Data Recieved", "Data Sent", "Data/Flow"],
		radius = rfun[2];
	var xind = 0,
		yind = 1,
		rind = 2;
	function color(d) { return +d.f; }
	function key(d) { return d.p; }
	// Chart dimensions.
	var width = m.w - m.mr - m.ml,
		height = m.h - m.mt - m.mb;
	// Various scales. These domains make assumptions of data, naturally.
	var xScale = d3.scale.log().range([0, width]),
		yScale = d3.scale.log().range([height, 0]),
		radiusScale = d3.scale.sqrt().range([2, 40]),
		colorScale = d3.scale.category10(),
		dotg,
		data = dat;

	// The x & y axes.
	var xAxis = d3.svg.axis().orient("bottom").scale(xScale).ticks(12, d3.format(",s")),
		yAxis = d3.svg.axis().scale(yScale).orient("left").ticks(10, d3.format(",s"));

	// Create the SVG container and set the origin.
	var svg = d3.select(place).append("svg")
			.attr("width", width + m.ml + m.mr)
			.attr("height", height + m.mt + m.mb)
			.append("g")
				.attr("transform", "translate(" + m.ml + "," + m.mt + ")");

	// Add the x-axis.
	var xax = svg.append("g")
			.attr("class", "x axis")
			.attr("transform", "translate(0," + height + ")");

	// Add the y-axis.
	var yax = svg.append("g")
			.attr("class", "y axis");

	// Add an x-axis label.
	var xlab = svg.append("text")
			.attr("class", "x label")
			.attr("text-anchor", "end")
			.attr("x", width)
			.attr("y", height - 6)
			.text(xylabels[xind])
			.on("click", changex);

	// Add a y-axis label.
	var ylab = svg.append("text")
			.attr("class", "y label")
			.attr("text-anchor", "end")
			.attr("y", 6)
			.attr("dy", ".75em")
			.attr("transform", "rotate(-90)")
			.text(xylabels[yind])
			.on("click", changey);

	// Add an r label.
	var rlab = svg.append("text")
			.attr("class", "r label")
			.attr("text-anchor", "end")
			.attr("x", width)
			.attr("y", 36)
			.text(rlabels[rind])
			.on("click", changer);

	// Add chart
	var chart = svg.append("g")
			.attr("class", "dots");

	// Add info elements
	var info = svg.append("g")
			.attr("class", "info")
			.style("opacity", 0);
	info.append("circle")
		.style("opacity", 0.5)
		.style("fill", "#000000")
		.style("stroke-width", 3)
		.attr("r", 0);
	var ptitle = info.append("text")
			.attr("text-anchor", "middle")
			.attr("y", -40)
			.text("");
	info.append("text")
		.attr("text-anchor", "middle")
		.attr("x", -35)
		.attr("y", -20)
		.text("IP src");
	info.append("text")
		.attr("text-anchor", "middle")
		.attr("x", 35)
		.attr("y", -20)
		.text("IP dst");
	var pips = info.append("text")
			.attr("text-anchor", "middle")
			.attr("x", -35)
			.attr("y", -7)
			.text("");
	var pipd = info.append("text")
			.attr("text-anchor", "middle")
			.attr("x", 35)
			.attr("y", -7)
			.text("");
	var pdat = info.append("text")
		.attr("text-anchor", "middle")
		.attr("x", 0)
		.attr("y", 7)
		.text("Data");
	info.append("rect")
		.attr('x', -56)
		.attr('y', 12)
		.attr("fill", "#00C333")
		.attr("stroke", "white")
		.attr('height', 13)
		.attr('width', 112);
	var prec = info.append("rect")
			.attr('x', -56)
			.attr('y', 12)
			.attr("fill", "#CC3333")
			.attr("stroke", "white")
			.attr('height', 13)
			.attr('width', 50);
	info.append("text")
		.attr("text-anchor", "middle")
		.attr("x", 0)
		.attr("y", 40)
		.text("Flows");
	var pflows = info.append("text")
			.attr("text-anchor", "middle")
			.attr("x", 0)
			.attr("y", 55)
			.text("");
	info.append("circle")
		.attr("r", 70)
		.style("opacity", 0)
		.on("mouseout", mouseout);

	// Add basic object (for animation)
	xScale.domain([0.5, d3.max(data, x)*5]);
	yScale.domain([0.5, d3.max(data, y)*5]);
	radiusScale.domain([0.5, d3.max(data, radius)]);
	xax.call(xAxis);
	yax.call(yAxis);

	// Add a dot per protocol
	dotg = chart.selectAll(".dot")
			.data(data)
		.enter().append("g")
			.attr("transform", function(d) { return "translate(" + xScale(y(d)) + "," + height + ")";});

	// Add label
	dotg.append("text")
			.attr("class", "label")
			.attr("text-anchor", "middle")
			.attr("dy", ".3em")
			.text(function(d) { return key(d); });

	// Add circle
	dotg.append("circle")
			.attr("class", "dot")
			.style("fill", function(d) { return colorScale(color(d)); })
			.attr("r", 10)
			.on("mouseover", mouseover);
	// Animation
	dotg.transition()
			.duration(1000)
			.call(position).sort(order);
	// move info outside chart
	info.attr("transform", "translate(-100,-100)");
	
	// Set of functions for the transition
	// Positions the dots based on data.
	function position(elem) {
		elem.attr("transform", function(d) { return "translate(" + xScale(x(d)) + "," + yScale(y(d)) + ")";});
		elem.select(".dot")
			.attr("r", function(d) {return radiusScale(radius(d)); });
		elem.select("text")
			.attr("y", function(d) {return -radiusScale(radius(d))-6; });
	}

	// Defines a sort order so that the smallest dots are drawn on top.
	function order(a, b) {
		return radius(b) - radius(a);
	}

	// Info show function
	function mouseover(d) {
		info.style("opacity", 0)
			.attr("transform", "translate(" + xScale(x(d)) + "," + yScale(y(d)) + ")")
		info.select("circle")
			.attr("r", radiusScale(radius(d)))
			.style("stroke", colorScale(color(d)));
		prec.attr('width', 0);
		pips.text(SizeBMG(d.ips, 1000));
		pipd.text(SizeBMG(d.ipd, 1000));
		pflows.text(SizeBMG(d.f, 1000));
		pdat.text("Data: "+SizeBMG(+d.i+(+d.o)));
		ptitle.text(d.p);
	
		info.transition()
			.duration(500)
			.style("opacity", 1);
		info.select("circle")
			.transition()
			.duration(300)
			.attr("r", 70);
		prec.transition()
			.duration(550)
			.attr('width', 112*(+d.o/(+d.o+(+d.i))));
	}

	function mouseout(d) {
		info.transition()
			.duration(300)
			.style("opacity", 0);
		info.select("circle")
			.transition()
			.duration(500)
			.attr("r", 40);
	
		info.transition()
			.delay(300)
			.attr("transform", "translate(-100,-100)");
	}

	// Change Axis scale
	function changer() {
		rind++;
		rind = rind%7;
		radius = rfun[rind];
		radiusScale.domain([0, d3.max(data, radius)]);
		rlab.text(rlabels[rind]);		
		dotg.transition()
			.duration(1000)
			.call(position).sort(order);
	}
	
	function changex() {
		xind++;
		xind = xind%4;
		x = xyfun[xind];
		xScale.domain([0.5, d3.max(data, x)*5]);	
		xax.call(xAxis);
		xlab.text(xylabels[xind]);	
		dotg.transition()
			.duration(1000)
			.call(position).sort(order);
	}
	
	function changey() {
		yind++;
		yind = yind%4;
		y = xyfun[yind];
		yScale.domain([0.5, d3.max(data, y)*5]);
		yax.call(yAxis);
		ylab.text(xylabels[yind]);	
		
		dotg.transition()
			.duration(1000)
			.call(position).sort(order);
	}
}
