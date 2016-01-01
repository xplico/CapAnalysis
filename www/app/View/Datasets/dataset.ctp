<!--
   CapAnalysis

   Copyright 2012 Gianluca Costa (http://www.capanalysis.net) 
   All rights reserved.
-->

		<table cellpadding="0" cellspacing="0" class="fixed">
		<tr>
			<th class="size"><?php echo $this->Paginator->sort('id', __('Id', true), array('title'=>__('Sort by Identification Number', true))); ?></th>
			<th class=""><?php echo $this->Paginator->sort('name', __('Name', true), array('title'=>__('Sort by Name', true))); ?></th>
            <th class=""><?php echo __('Limit'); ?></th>
			<th class="size"><?php echo __('Files'); ?></th>
			<th class="size"><?php echo __('Size'); ?></th>
            <!--<th class="size"></th>-->
			<th class="size"></th>
			<th class="txt-cent"><?php echo __('Actions'); ?></th>
		</tr>
		<?php
        $demo = $this->Session->check('demo');
        foreach ($datasets as $dataset):
        $vals = explode(':', $dataset['Dataset']['depth']);
        switch ($vals[0]) {
        case 'EOL':
            $depth = $vals[1];
            break;
        case 'TD':
            $depth = $vals[1].' s';
            break;
        case 'FD':
            $depth = $vals[1].' '.__('flows');
            break;
        case 'SZ':
            $depth = $vals[1].' MB';
            break;
        default:
            $depth = '---';
        }
        ?>
		<tr>
			<td><?php echo h($dataset['Dataset']['id']); ?>&nbsp;</td>
			<?php if ($dataset[0]['fcnt']): ?>
			<td class=""><strong><?php echo $this->Html->link($dataset['Dataset']['name'], array('controller' => 'items', 'action' => 'index', $dataset['Dataset']['id'])); ?></strong>&nbsp;</td>
			<?php else: ?>
			<td class=""><?php echo $dataset['Dataset']['name']; ?>&nbsp;</td>
			<?php endif; ?>
            <td class=""><?php echo $depth; ?>&nbsp;</td>
			<td><?php echo h($dataset[0]['fcnt']); ?>&nbsp;</td>
			<td class="size"><?php echo h($this->String->size($dataset[0]['fsize'])); ?>&nbsp;</td>
		    <!-- <td class="cursor"><?php echo $this->Html->image('share.png', array('alt' => '', 'title' => __('Share'), 'class' => 'share', 'data-id' => $dataset['Dataset']['id']));?></td>-->
		    <td class="cursor"><?php echo $this->Html->image('pie_ds.png', array('alt' => '', 'title' => __('Flow Info and Pcap'), 'class' => 'jpie', 'data-id' => $dataset['Dataset']['id']));?></td>
            <td class="actions">
				<?php echo $this->Html->link(__('Files'), array('controller' => 'capfiles', 'action' => 'index', $dataset['Dataset']['id'])); ?>
                <?php if (!$demo): ?>
				<?php   echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $dataset['Dataset']['id']), null, __('Are you sure you want to delete "%s"?', $dataset['Dataset']['name'])); ?>
                <?php endif;?>
			</td>
		</tr>
		<?php endforeach; ?>
		</table>
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
<script>
	var pathurl = $('.paging a:first').attr("href");
	if (pathurl) {
		pathurl = pathurl.replace(/\/page:[0-9]*/g, "");
		if (pathurl.search("ets/index") == -1)
			pathurl += "/index";
		$("#dial").dial({
			'angleArc': 250,
			'angleOffset': -125,
			'bgColor':"#DFEDED",
			'fgColor':"#3474EF",
			'change' : function (v) {
				$('#gopage').removeClass('disabled');
				$('#gopage a').attr("href", pathurl+"/page:"+v);
				//$('#gopage a').attr("href", "<?php echo $this->Html->url(array('controller' => 'datasets', 'action' => 'index', 'page')); ?>:"+v);
			}
		});
	}
    $('table a[title]').qtip({position: {my: 'bottom center', at: 'top center'}, style: {classes: 'ui-tooltip-shadow ui-tooltip-dark'}});
	$('.jpie').each(function() {
		$(this).qtip({position: {my: 'left center', at: 'right center'},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-light'},
					content: {text: 'Loading...', ajax: {url: '<?php echo $this->Html->url(array('controller' => 'datasets', 'action' => 'datatip')); ?>/'+$(this).attr("data-id") }, title: {text: 'DataSet Report', button: true}},
					show: {event: 'click',	solo: true},
					hide: 'unfocus'
		});
	});
    $('.share').each(function() {
		$(this).qtip({position: {my: 'bottom center', at: 'top center'},
					style: {classes: 'ui-tooltip-shadow ui-tooltip-light'},
					content: {text: 'Loading...', ajax: {url: '<?php echo $this->Html->url(array('controller' => 'datasets', 'action' => 'sharetip')); ?>/'+$(this).attr("data-id") }, title: {text: 'Share URL', button: true}},
					show: {event: 'click',	solo: true},
					hide: 'unfocus'
		});
	});
    
	/* load new page */
	$("th a, .page-bar a").unbind('click');
	$("th a, .page-bar a").click(function(e) {
		e.preventDefault( );
		$.ajax({
			url: $(this).attr('href'),
			success: function(data) {
				$('#ui-tabs-1').html(data);
			}
		});
	});
</script>
