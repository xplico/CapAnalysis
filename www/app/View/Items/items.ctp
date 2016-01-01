<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->
<div id="items">
	    <div class="tabw <?php if (count($items) < 101) echo 'scrollx'; else echo 'scrollxy'; ?> btbar">
		<table cellpadding="0" cellspacing="0" class="nobor txt-cent">
		<thead class="fixed">
		<tr>
			<?php if ($columns[0]): ?>
		    <th></th>
		    <?php endif; ?>
			<?php if ($columns[1]): ?>
			<th><?php echo $this->Paginator->sort('cdate', __('Date'), array('title'=>__('Sort by Date'))); ?></th>
			<th><?php echo $this->Paginator->sort('ctime', __('Time'), array('title'=>__('Sort by Time'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[3]): ?>
			<th><?php echo $this->Paginator->sort('ip_src', __('Source IP'), array('title'=>__('Sort by Source IP'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[4]): ?>
			<th><?php echo $this->Paginator->sort('ip_dst', __('Destination IP'), array('title'=>__('Sort by Destination IP'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[5]): ?>
			<th><?php echo $this->Paginator->sort('dns', __('Destination Name'), array('title'=>__('Sort by Destination Name'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[7]): ?>
			<th><?php echo $this->Paginator->sort('port_src', __('Source Port'), array('title'=>__('Sort by Source Port'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[8]): ?>
			<th><?php echo $this->Paginator->sort('port_dst', __('Destination Port'), array('title'=>__('Sort by Destination Port'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[9]): ?>
			<th><?php echo $this->Paginator->sort('l4prot', __('L4'), array('title'=>__('Sort by Transport Layer'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[10]): ?>
			<th><?php echo $this->Paginator->sort('l7prot', __('Protocol'), array('title'=>__('Sort by Protocol'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[11]): ?>
			<th><?php echo $this->Paginator->sort('country', __('Country'), array('title'=>__('Sort by Country'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[12]): ?>
			<th><?php echo $this->Paginator->sort('bsent', __('Bytes Sent'), array('title'=>__('Sort by Bytes Sent'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[13]): ?>
			<th><?php echo $this->Paginator->sort('brecv', __('Bytes Received'), array('title'=>__('Sort by Bytes Received'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[14]): ?>
			<th><?php echo __('Bytes %'); ?></th>
			<?php endif; ?>
			<?php if ($columns[15]): ?>
			<th><?php echo $this->Paginator->sort('blsent', __('Lost bytes Sent'), array('title'=>__('Sort by Lost bytes Sent'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[16]): ?>
			<th><?php echo $this->Paginator->sort('blrecv', __('Lost bytes Received'), array('title'=>__('Sort by Lost bytes Received'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[17]): ?>
			<th><?php echo $this->Paginator->sort('pktsent', __('Packets Sent'), array('title'=>__('Sort by Packets Sent'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[18]): ?>
			<th><?php echo $this->Paginator->sort('pktrecv', __('Packets Received'), array('title'=>__('Sort by Packets Received'))); ?></th>
			<?php endif; ?>
			<?php if ($columns[19]): ?>
			<th><?php echo __('Packets %'); ?></th>
			<?php endif; ?>
			<?php if ($columns[20]): ?>
			<th><?php echo $this->Paginator->sort('duration', __('Duration'), array('title'=>__('Sort by Duration'))); ?></th>
			<?php endif; ?>
		</tr>
		</thead>
		<?php
		foreach ($items as $item): ?>
		<tr>
			<?php if ($columns[0]): ?>
		    <td class="cursor"><?php echo $this->Html->image('info.png', array('alt' => '', 'title' => __('Flow Info and Pcap'), 'data-url' => $this->Html->url(array('controller' => 'items', 'action' => 'info', $item['Item']['id'])))); ?></td>
			<?php endif; ?>
			<?php if ($columns[1]): ?>
			<td><?php echo h($item['Item']['cdate']); ?>&nbsp;</td>
			<td><?php echo h($item['Item']['ctime']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[3]): ?>
			<td class="cursor" data-title="<?php echo __('IP').': '.$item['Item']['ip_src']; ?>" data-url="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdata', $item['Item']['ips_id']));?>"><?php echo h($item['Item']['ip_src']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[4]): ?>
			<td class="cursor" data-title="<?php echo __('IP').': '.$item['Item']['ip_dst']; ?>" data-url="<?php echo $this->Html->url(array('controller' => 'items', 'action' => 'ipdata', $item['Item']['ipd_id']));?>"><?php echo h($item['Item']['ip_dst']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[5]): ?>
			<td><span title="<?php echo 'IP:'.h($item['Item']['ip_dst']); ?>"><?php echo h($item['Item']['dns']); ?>&nbsp;</span></td>
			<?php endif; ?>
			<?php if ($columns[7]): ?>
			<td><?php echo h($item['Item']['port_src']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[8]): ?>
			<td><?php echo h($item['Item']['port_dst']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[9]): ?>
			<td><?php echo h($item['Item']['l4prot']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[10]): ?>
			<td><?php echo h($item['Item']['l7prot']); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[11]): ?>
			<td><div class="bubble"><?php echo h($item['Item']['country']); ?></div>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[12]): ?>
			<td><div class="bubble red"><?php echo h($this->String->size($item['Item']['bsent'])); ?></div>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[13]): ?>
			<td><div class="bubble green"><?php echo h($this->String->size($item['Item']['brecv'])); ?></div>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[14]): ?>
			<td><span class="pie"><?php echo $item['Item']['bsent'].','.$item['Item']['brecv']; ?></span></td>
			<?php endif; ?>
			<?php if ($columns[15]): ?>
			<td><?php echo h($this->String->size($item['Item']['blsent'])); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[16]): ?>
			<td><?php echo h($this->String->size($item['Item']['blrecv'])); ?>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[17]): ?>
			<td><div class="bubble red"><?php echo h($item['Item']['pktsent']); ?></div>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[18]): ?>
			<td><div class="bubble green"><?php echo h($item['Item']['pktrecv']); ?></div>&nbsp;</td>
			<?php endif; ?>
			<?php if ($columns[19]): ?>
			<td><span class="pie"><?php echo $item['Item']['pktsent'].','.$item['Item']['pktrecv']; ?></span></td>
			<?php endif; ?>
			<?php if ($columns[20]): ?>
			<td><?php echo h($item['Item']['duration']); ?>&nbsp;</td>
			<?php endif; ?>
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
					<input id="dial" type="text" value="<?php echo $page_info['page']?>" data-width="50" data-min="1" data-max="<?php echo $page_info['pageCount']?>">
				</div>
				<div class="paging">
					<span id="gopage" class="alone disabled"><a href="#"><?php echo __('Go'); ?></a></span>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="clear">&nbsp;</div>
		<div id="items_load" class="tab_warn dispoff"><?php echo _('Wait...'); ?></div>
</div>
<script>
$(function() {
	var pathurl = $('#items .paging a:first').attr("href");
	if (pathurl) {
		pathurl = pathurl.replace(/\/page:[0-9]*/g, "");
		if (pathurl.search("ms/index") == -1)
			pathurl += "/index";
		$("#dial").dial({
			'angleArc': 250,
			'angleOffset': -125,
			'bgColor':"#DFEDED",
			'fgColor':"#3474EF",
			'change' : function (v) {
				$('#gopage').removeClass('disabled');
				$('#gopage a').attr("href", pathurl+"/page:"+v);
			}
		});
	}
	// tip
	$('#items table a[title], #items span[title]').qtip({position: {my: 'bottom center', at: 'top center'}, style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'}});
    $('#items td img').each(function() {
		$(this).qtip({
					position: {my: 'left top', at: 'right center'},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-light ui-tooltip-large'},
					content: {text: 'Loading...', ajax: {url: $(this).attr("data-url") }, title: {text: $(this).attr('title'), button: true}},
					show: {event: 'click',	solo: true},
					hide: 'unfocus'
		});
	});
	// modal window
	/*
	$('#items td[data-url]').each(function() {
		$(this).qtip({
					position: {my: 'top center', at: 'top center', target: $('body')},
					hide: {event: false},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-light ui-tooltip-exlarge'},
					content: {text: 'Loading...', ajax: {url: $(this).attr("data-url") }, title: {text: $(this).attr('title'), button: true}},
					show: {
						event: 'click',
						solo: true,
						modal: {on: true, blur: false, escape: false}
					}
		});
	});
	*/
	var modalwd = $('#items td[data-url]');
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
	
	$("#items .pie").sparkline('html', {type: 'pie', sliceColors: ['#FF5050', '#00A400'], borderWidth: 1, borderColor: '#000000'});
	$("#items tr").click(function () {
		if ($(this).hasClass("clicked")) {
			$(this).removeClass("clicked");
		}
		else {
			$(this).addClass("clicked");
		}
	});
	/* load new page */
	$('#items th a, #items .page-bar a').unbind('click');
	$("#items th a, #items .page-bar a").click(function(e) {
		e.preventDefault( );
		$("#items_load").fadeIn();
		$.ajax({
			url: $(this).attr('href'),
			success: function(data) {
				$('#ui-tabs-1').html(data);
			}
		});
	});
});
</script>
