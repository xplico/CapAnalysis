<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div class="flowinfo">
<h2><?php echo __('Raw Data'); ?></h2>
<p>
<a href="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'pcap', $id)); ?>"><?php echo $this->Html->image('download.png', array('alt' => '')); ?><span class="download"><?php echo __('pcap File'); ?></span></a>
</p>
<h2><?php echo __('Protocol Stack'); ?></h2>
<div>
	<h3>File:</h3>
	<p>
	<span>file.name:</span> <span class="data"><?php echo $filename;?></span><br />
	</p>
</div>
<div>
	<h3>IP:</h3>
	<p>
	<span>ip.src:</span> <span class="data"><?php echo $info['Item']['ip_src'];?></span><br />
	<span>ip.dst:</span> <span class="data"><?php echo $info['Item']['ip_dst'];?></span><br />
	</p>
</div>
<div>
	<h3><?php echo $info['Item']['l4prot'];?>:</h3>
	<p>
	<span><?php echo $info['Item']['l4prot'];?>.srcport:</span> <span class="data"><?php echo $info['Item']['port_src'];?></span><br />
	<span><?php echo $info['Item']['l4prot'];?>.dstport:</span> <span class="data"><?php echo $info['Item']['port_dst'];?></span><br />
	</p>
</div>
<div>
	<h3><?php echo __('Encapsulation'); ?>:</h3>
	<p>
	<span><?php echo $info['Item']['encaps'];?></span><br />
	</p>
</div>
</div>
