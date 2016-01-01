
<hr/>
<div class="row">
<div class="span4 pagination-centered">
	<a class="btn btn-large btn-success" href="<?php echo $ROOT_APP.'manual/files'; ?>"><?php echo _('PCAP Files');?></a>
</div>
<div class="span3 pagination-centered">
	<a class="btn btn-large" href="<?php echo $ROOT_APP.'manual/data_view'; ?>"><?php echo _('Displaying Data');?></a>
</div>
<div class="span4 pagination-centered">
	<a class="btn btn-large btn-danger" href="<?php echo $ROOT_APP.'manual/menu'; ?>"><?php echo _('Menù');?></a>
</div>
</div>
<hr/>
<div class="row">
<ul class="thumbnails">
	<li class="span8">
		<div class="thumbnail">
		<img alt="" src="<?php echo $ROOT_APP;?>/images/main.png">
		</div>
	</li>
	<li class="span4">
		<h3>Dataset</h3>
		<p>CapAnalysis is able to manage a set of Dataset. For each Dataset there is the possibility to:
		<ul>
			<li>Upload pcap files, directly from the WUI or with PCAP-over-IP connection</li>
			<li>View the all "flows" (TCP and UDP)</li>
			<li>See what types of protocols there are in the Dataset</li>
			<li>Give information about geographical location of the connections</li>
			<li>Filter the data</li>
			<li>Extract all the packets of a single flow</li>
			<li>The number of bytes lost (TCP)</li>
			<li>...</li>
		</ul>
		</p>
    </li>
</ul>
</div>
