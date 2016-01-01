<!--
   CapAnalysis

   Copyright 2012-2015 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div class="outcome">
	<div class="outcome-bord">
    <ul>
      <li><strong><?php echo __('The dataset and its data are visible only from your IP'); ?>:</strong> <strong class="bubble red"><?php echo $_SERVER['REMOTE_ADDR']; ?></strong></li>
      <li><strong><?php echo __('All data and files uploaded will be removed in the date indicated in "Limit" column (UTC Time)'); ?></strong></li>
      <li><strong><?php echo __('The dataset size limit is'); ?>:</strong> <strong class="bubble blue">20M byte</strong></li>
      <li>-</li>
      <li><?php echo __('Max pcap file size'); ?>: <strong>20M byte</strong></li>
      <li><strong><?php echo __("While the decoded data are not publicly shared, we make no claims that your data is not viewable by other CapAnalysis users."); ?></strong></li>
      <li>-</li>
    </ul> 
	</div>
</div>
