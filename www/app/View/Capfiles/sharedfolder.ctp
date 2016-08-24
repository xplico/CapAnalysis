<!--
   CapAnalysis

   Copyright 2016 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php if ($this->Session->check('demo')): ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __("Shared folder isn't enabled on CapAnalysis Demo"); ?></h2>
	</div>
</div>
<?php else : ?>
<div class="outcome">
	<div class="outcome-bord">
	<h2><?php echo __('PCAP file using shared folder'); ?></h2><br/><br/>
	<?php if ($capana) : ?>
	<p><?php echo __('You can copy your pcap files on DataSet "<strong>'.$dataset_name.'</strong>" using this shared folder:'); ?></p>
	<br/><p>From Windows <strong><?php echo $shared_folder;?></strong></p>
	<p>From Linux <strong><?php echo $shared_folder_linux;?></strong></p><br/>
	<?php else : ?>
	<p class="bubble red"><?php echo __('The CapAnalysis daemon is not running... then it can not accept new files.'); ?></p><br/><br/>
	<?php endif; ?>
	</div>
</div>
<?php endif; ?>
