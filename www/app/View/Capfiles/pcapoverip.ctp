<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php if ($this->Session->check('demo')): ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __("PCAP-over-IP isn't enabled on CapAnalysis Demo"); ?></h2>
	</div>
</div>
<?php else : ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __('Pcap file with PCAP-over-IP'); ?></h2><br/><br/>
	<?php if ($capana) : ?>
	<p><?php echo __('You can upload your pcap files on DataSet "<strong>'.$dataset_name.'</strong>" using this command:'); ?></p>
	<br/><p><strong>cat your_file.pcap | nc <?php echo $ip.'  '.$port;?></strong></p><br/>
	<?php else : ?>
	<p class="bubble red"><?php echo __('The CapAnalysis daemon is not running... then it can not accept new files.'); ?></p><br/><br/>
	<?php endif; ?>
	</div>
</div>
<?php endif; ?>
