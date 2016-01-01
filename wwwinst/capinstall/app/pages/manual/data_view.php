
<ul class="thumbnails">
	<li class="span12">
		<div class="thumbnail">
		<h4 class="pagination-centered">Visualization tabs</h4>
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/tabs.png">
		</div>
    </li>
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/flows.png">
		</div>
    </li>
	<li class="span6">
		<h4>Flows</h4>
		<p>
			List of all streams (UDP and TCP) present in the Dataset. For each flow/stream will be indicated:
			<div class="row">
				<div class="span3">
				<ul>
				 <li>Date and time</li>
				 <li>Source IP</li>
				 <li>Destiantion IP</li>
				 <li>Hostname -destination-</li>
				 <li>Source port</li>
				 <li>Destiantion port</li>
				 <li>Layer 4 protocol</li>
				 <li>Application protocol</li>
				</ul>
				</div>
				<div class="span3">
				<ul>
				 <li>Country</li>
				 <li>Bytes sent</li>
				 <li>Byte received</li>
				 <li>Lost bytes Sent</li>
				 <li>Lost bytes Received</li>
				 <li>Packets Sent</li>
				 <li>Packets Received</li>
				 <li>Duration</li>
				</ul>
				</div>
			</div>
		</p>
    </li>
</ul>
<hr/>
<ul class="thumbnails">
	<li class="span6">
		<h4>Overview</h4>
		<p>
			"Overview" shows the distribution of destination port used in the Dataset's flows, and also: the hourly distribution of the data, the protocol type vs the country and the protocol type vs the data (days).
			For each of these charts you can select (from the small menu near the chart) the visualization type.
			
		</p>
    </li>
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/overview.png">
		</div>
    </li>
</ul>
<hr/>
<ul class="thumbnails">
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/statistics.png">
		</div>
    </li>
	<li class="span6">
		<h4>Statistics</h4>
		<p>
			"Statistics provides detailed information and the possibility to quickly identify:
			<ul>
				<li>Which IP generates the most traffic</li>
				<li>The most used destination IP</li>
				<li>The protocols being used</li>
				<li>The duration of the connections</li>
			</ul>
		</p>
    </li>
</ul>
<hr/>
<ul class="thumbnails">
	<li class="span6">
		<h4>Per Hour</h4>
		<p>
			For each day, for each hour "Per Hour" shows:
			<ul>
				<li>the data sent and received</li>
				<li>for every 5 minutes the number of IP sources present and the number of IP destinations contacted</li>
				<li>for every 5 minutes the data sent and received</li>
				<li>the ports destiantion used by the connections</li>
				<li>the protocols used vs destination countries</li>
			</ul>
		</p>
    </li>
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/perhour.png">
		</div>
    </li>
</ul>
<hr/>
<ul class="thumbnails">
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/geomap.png">
		</div>
    </li>
	<li class="span6">
		<h4>GeoMAP</h4>
		<p>
			A map of the world where the color of the nation identifies the level of intensity of the flows, or data received or sent data
		</p>
    </li>
</ul>

<hr/>
<ul class="thumbnails">
	<li class="span6">
		<h4>IP sources and destinations</h4>
		<p>
			List of all IP source/destination. Clicking on the IP you can have a detail and analysis of data related to that IP.
			<img class="mlen span5" alt="" src="<?php echo $ROOT_APP;?>/images/ip_data1.png">
		</p>
    </li>
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/ipsources.png">
		</div>
    </li>
</ul>

<hr/>

<ul class="thumbnails">
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/protocols.png">
		</div>
    </li>
	<li class="span6">
		<h4>Protocols</h4>
		<p>
			A chart which displays data protocols based on (click on the name of the background):
			<div class="row">
				<div class="span2">
				<ul>
				<li>Data</li>
				<li>Flows</li>
				<li>Data Received</li>
				<li>Data Sent</li>
				<li>Data/Flow</li>
				<li>IP Destination</li>
				<li>IP Source</li>
				</ul>
				</div>
				<div class="span4">
				Clicking on the name of the axes (X, Y) it is possible to change the reference of each axis, and clicking on the protocol spot appear other informations.
				<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/protocol_spot.png">
				</div>
			</div>
		</p>
    </li>
</ul>

<hr/>
<ul class="thumbnails">
	<li class="span6">
		<h4>Time Line</h4>
		<p>
			The chats show the number of connectons, the amount of data exchanged the data sent and received.<br/>
			Timeline charts have a minimal resolution of 5 minutes.
		</p>
    </li>
	<li class="span6">
		<div class="thumbnail">
		<img class="mlen" alt="" src="<?php echo $ROOT_APP;?>/images/timeline.png">
		</div>
    </li>
</ul>
