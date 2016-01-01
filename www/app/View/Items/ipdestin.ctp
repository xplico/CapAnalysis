<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="ipdestin">
	    <div class="tabw <?php if (count($items) > 100) echo 'scrollxy'; ?> btbar">
	    <div style="position: absolute; right: 0;">
			<div id="reload_ipd" class="treload"><a href="#"><?php echo $this->Html->image('reload.png', array('alt' => '')); ?></a></div>
		</div>
		<table cellpadding="0" cellspacing="0" class="nobor">
		<thead class="fixed">
		<tr>
			<th style="width: 350px"><?php echo $this->Paginator->sort('ipd', __('IP'), array('title'=>__('Sort by IP'))); ?></th>
			<th><?php echo $this->Paginator->sort('fcnt', __('Flows'), array('title'=>__('Sort by Flows'))); ?></th>
			<th><?php echo $this->Paginator->sort('bin', __('Bytes Sent'), array('title'=>__('Sort by Bytes Sent'))); ?></th>
			<th><?php echo $this->Paginator->sort('bout', __('Bytes Received'), array('title'=>__('Sort by Bytes Received'))); ?></th>
			<th><?php echo __('Pies %'); ?></th>
		</tr>
		</thead>
		<?php
		foreach ($items as $item): ?>
		<tr>
			<td class="cursor" data-title="<?php echo __('IP').': '.$item['Item']['ipd']; ?>" data-url="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdata', $item['Item']['ipd_id']));?>"><?php echo h($item['Item']['ipd']); ?>&nbsp;</td>
			<td><?php echo h($this->String->num($item['Item']['fcnt'])); ?>&nbsp;</td>
			<td><div class="bubble green"><?php echo h($this->String->size($item['Item']['bin'])); ?></div>&nbsp;</td>
			<td><div class="bubble red"><?php echo h($this->String->size($item['Item']['bout'])); ?></div>&nbsp;</td>
			<td><span class="pie"><?php echo $item['Item']['bin'].','.$item['Item']['bout']; ?></span></td>
		</tr>
		<?php endforeach; ?>
		</table>
		</div>
		<div class="page-bar">
			<div class="page-bar-bord">
				<div class="paging">
				<?php
					echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
					echo $this->Paginator->numbers(array('separator' => ''));
					echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
				?>
				</div>
				<?php $page_info = $this->Paginator->params();?>
				<?php if ($page_info['pageCount'] > 9): ?>
				<div class="page-cursor">
					<input id="dial2" type="text" value="<?php echo $page_info['page']?>" data-width="50" data-min="1" data-max="<?php echo $page_info['pageCount']?>">
				</div>
				<div class="paging">
					<span id="gopage2" class="alone disabled"><a href="#"><?php echo __('Go'); ?></a></span>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="clear">&nbsp;</div>
		<div id="items_load2" class="tab_warn dispoff"><?php echo _('Wait...'); ?></div>
</div>
<script>
$(function() {
	var pathurl = $('#ipdestin .paging a:first').attr("href");
	if (pathurl) {
		pathurl = pathurl.replace(/\/page:[0-9]*/g, "");
		if (pathurl.search("ms/index") == -1)
			pathurl += "/index";
		$("#dial2").dial({
			'angleArc': 250,
			'angleOffset': -125,
			'bgColor':"#DFEDED",
			'fgColor':"#3474EF",
			'change' : function (v) {
				$('#gopage2').removeClass('disabled');
				$('#gopage2 a').attr("href", pathurl+"/page:"+v);
			}
		});
	}
	// tip
	$('#ipdestin table a[title], #ipdestin span[title]').qtip({position: {my: 'bottom center', at: 'top center'}, style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'}});
    
	var modalwd = $('#ipdestin td[data-url]');
	$('<div />').qtip({
		content: {text: ' ', title: {text:' ', button: true}},
		position: {
             my: 'top center', at: 'top center', target: $('body')
		},
		hide: {event: false},
		style: {classes: 'ui-tooltip-shadow ui-tooltip-light ui-tooltip-exlarge'},
		show: {
			target: modalwd,     
			event: 'click',
			solo: true,
			modal: {on: true, blur: false, escape: false}
		},
		events: {
			show: function(event, api) {
				// Update the content of the tooltip on each show
				var target = $(event.originalEvent.target); 
				api.set({
					'content.title.text': target.attr('data-title'),
					'content.text': '<?php echo __('Loading');?>...',
					'content.ajax.type': 'GET',
					'content.ajax.url': target.attr("data-url")
				}); 
			}
		}
	});
	
	$("#ipdestin .pie").sparkline('html', {type: 'pie', sliceColors: ['#FF5050', '#00A400'], borderWidth: 1, borderColor: '#000000'});
	
	/* load new page */
	$("#ipdestin th a, #ipdestin .page-bar a").click(function(e) {
		e.preventDefault( );
		$("#items_load2").fadeIn();
		$.ajax({
			url: $(this).attr('href'),
			success: function(data) {
				$('#ui-tabs-7').html(data);
			}
		});
	});
	
	/* reload the page */
	$('#reload_ipd').unbind('click');
	$("#reload_ipd").click(function() {
		$.ajax({
			url: "<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdestin')); ?>",
			context: document.body
		}).done(function(data) { 
			$("#ui-tabs-7").html(data);
		});
	});
});
</script>
