<div class="classifications index">
	<h2><?php echo __('Classifications'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th><?php echo $this->Paginator->sort('text_color'); ?></th>
			<th><?php echo $this->Paginator->sort('bg_color'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	foreach ($classifications as $classification): ?>
	<tr>
		<td><?php echo h($classification['Classification']['id']); ?>&nbsp;</td>
		<td><?php echo h($classification['Classification']['name']); ?>&nbsp;</td>
		<td><?php echo h($classification['Classification']['description']); ?>&nbsp;</td>
		<td><?php echo h($classification['Classification']['text_color']); ?>&nbsp;</td>
		<td><?php echo h($classification['Classification']['bg_color']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $classification['Classification']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $classification['Classification']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $classification['Classification']['id']), null, __('Are you sure you want to delete # %s?', $classification['Classification']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Classification'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Items'), array('controller' => 'items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Item'), array('controller' => 'items', 'action' => 'add')); ?> </li>
	</ul>
</div>
