<!DOCTYPE html>
<?php
$db = new mysqli('localhost', 'shiry', 'shiry', 'test');
$res = $db->query("DESC apt_fees");

$fields = array ();
while ($row = $res->fetch_object()) {
    $fields[] = $row->Field;
}

$res = $db->query("SELECT * FROM apt_fees ORDER BY date ASC");
$data = array ();

while ($row = $res->fetch_object()) {
    $data[] = $row;
}
?>
<html>
	<head>
		<title>d3</title>
		<style type="text/css">
		body {
		  font: 10px sans-serif;
		}

		.axis path,
		.axis line {
		  fill: none;
		  stroke: #000;
		  shape-rendering: crispEdges;
		}

		.x.axis path {
		  display: none;
		}

		.line {
		  fill: none;
		  stroke: steelblue;
		  stroke-width: 1.5px;
		}

		</style>

	</head>
	<body>
		<script type="text/javascript" src="/js/d3.min.js"></script>
		<script type="text/javascript">
		var data = <?=json_encode($data)?>;

		var margin = {top: 20, right: 100, bottom: 30, left: 40 },
		    width = 960 - margin.left - margin.right,
		    height = 700 - margin.top - margin.bottom;

		var parseDate = d3.time.format('%Y-%m-%d 00:00:00').parse;

		var x = d3.time.scale().range([0, width]);
		var y = d3.scale.linear().range([height, 0]);

		var color = d3.scale.category10();

		var xAxis = d3.svg.axis()
		            .scale(x).orient('bottom')
		            .ticks(d3.time.month)
		            .tickFormat(d3.time.format('%Y-%m'));
		var yAxis = d3.svg.axis().scale(y).orient('left');

		var line = d3.svg.line()
		            .x(function (d) { return x(d.date); })
		            .y(function (d) { return y(d.fee); });

		var svg = d3.select('body').append('svg')
		        .attr('width', width + margin.left + margin.right)
		        .attr('height', height + margin.top + margin.bottom)
		        .append('g')
		        .attr('transform','translate('+margin.left+','+margin.top +')');

		color.domain(d3.keys(data[0]).filter(function (key) { return (key !== 'date' &&
		            key !== 'id');
		        }));
		data.forEach(function (d, i) {
		    d.date = parseDate(d.date);
		});
		console.log(color.domain());
		var _list = color.domain().map(function (name) {
		    return {
		        name: name,
		        values: data.map(function (d) {
		            return { date: d.date, fee: +d[name] };
		        })
		    };
		});
		x.domain(d3.extent(data, function(d) { return d.date; }));
		y.domain([
		    d3.min(_list, function (c) {
		        return d3.min(c.values, function (v) {
		            return v.fee;
		        });
		    }),
		    d3.max(_list, function (c) {
		        return d3.max(c.values, function (v) {
		            return v.fee;
		        });
		    })
		]);

		svg.append('g')
		    .attr('class', 'x axis')
		    .attr('transform', 'translate(0,' + height + ')')
		    .call(xAxis);

		svg.append('g')
		    .attr('class', 'y axis')
		    .call(yAxis)
		    .append('text')
		    .attr('transform', 'rotate(-90)')
		    .attr('y', 6)
		    .attr('dy', '.71em')
		    .style('text-anchor', 'end')
		    .text('Fee');

		var city = svg.selectAll('.city')
		    .data(_list)
		    .enter().append('g')
		    .attr('class', 'city');
		city.append('path')
		    .attr('class', 'line')
		    .attr('d', function (d) { return line(d.values); })
		    .style('stroke', function (d) { return color(d.name); });
		city.append('text')
		    .datum(function (d) { return { name: d.name, value:
		            d.values[d.values.length - 1]}; })
		    .attr('transform', function (d) { return 'translate(' + x(d.value.date) +
		                ',' + y(d.value.fee) + ')'; })
		    .attr('x', 3)
		    .attr('dy', '.35em')
		    .text(function (d) { return d.name; });
		</script>
	</body>
</html>