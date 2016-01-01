<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div style="position: absolute; right: 0;">
	<div id="reload_ptcl" class="treload"><a href="#"><?php echo $this->Html->image('reload.png', array('alt' => '')); ?></a></div>
</div>
<div id="protocols" class="pgraph">
</div>
<script>
	var ptcld = JSON.parse('<?php echo $protocols; ?>'),
	ptclw = $("#protocols").parent().width()-1;
	protocolC("#protocols", ptcld, {w: ptclw, h: 460, mt: 35, mr: 5, mb: 35, ml: 35});
	
	/* reload the page */
	$('#reload_ptcl').unbind('click');
	$("#reload_ptcl").click(function() {
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'protocols')); ?>",
			context: document.body
		}).done(function(data) { 
			$("#ui-tabs-8").html(data);
		});
	});
</script>
