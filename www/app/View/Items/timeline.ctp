<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<?php if ($full) : ?>
<div style="position: absolute; right: 0;">
	<div id="reload_tl" class="treload"><a href="#"><?php echo $this->Html->image('reload.png', array('alt' => '')); ?></a></div>
</div>

<div id="timeline">
	<p><strong>Connections</strong></p>
    <div id="ftimeline"></div>
	<p><strong>Data</strong></p>
    <div id="ttimeline"></div>
	<p><strong>Data Received</strong></p>
    <div id="itimeline"></div>
	<p><strong>Data Sent</strong></p>
    <div id="otimeline"></div>
</div>
<script>
	var tld = JSON.parse('<?php echo $timeline; ?>'),
	wtl = $("#ftimeline").parent().width()-1;
	timeline("#ftimeline", tld.f, tld.n, {w: wtl, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'ra'});
	timeline("#itimeline", tld.i, tld.n, {w: wtl, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'rb'});
	timeline("#otimeline", tld.o, tld.n, {w: wtl, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'rc'});
	timeline("#ttimeline", tld.t, tld.n, {w: wtl, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'rd'});

	/* reload the page */
	$('#reload_tl').unbind('click');
	$("#reload_tl").click(function() {
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'timeline')); ?>",
			context: document.body
		}).done(function(data) { 
			$("#ui-tabs-9").html(data);
		});
	});
</script>
<?php else: ?>
<div id="timeline_emb">
	<p><strong>Connections</strong></p>
    <div id="ftimeline_emb"></div>
	<p><strong>Data</strong></p>
    <div id="ttimeline_emb"></div>
	<p><strong>Data Received</strong></p>
    <div id="itimeline_emb"></div>
	<p><strong>Data Sent</strong></p>
    <div id="otimeline_emb"></div>
</div>
<script>
	var tld_emb = JSON.parse('<?php echo $timeline; ?>'),
	wtl_emb = $("#ftimeline_emb").parent().width()-1;
	timeline("#ftimeline_emb", tld_emb.f, tld_emb.n, {w: wtl_emb, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'ra'});
	timeline("#itimeline_emb", tld_emb.i, tld_emb.n, {w: wtl_emb, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'rb'});
	timeline("#otimeline_emb", tld_emb.o, tld_emb.n, {w: wtl_emb, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'rc'});
	timeline("#ttimeline_emb", tld_emb.t, tld_emb.n, {w: wtl_emb, h: 110, mt: 10, mr: 10, mb: 3, ml: 30, c: 'rd'});
</script>
<?php endif; ?>
