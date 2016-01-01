<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="contree" class="gtree"></div>
<script>
	var ip_conn = JSON.parse('<?php echo $prot_cntrs; ?>'),
		wcon = $("#contree").parent().width()-1;
		
	gtree("#contree", ip_conn, {w: wcon, h: 20, mt: 10, mr: 10, mb: 30, ml: 30});
</script>
