<div class="classifications view">
<h2><?php  echo __('Classification'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($classification['Classification']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($classification['Classification']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($classification['Classification']['description']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Text Color'); ?></dt>
		<dd>
			<?php echo h($classification['Classification']['text_color']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Bg Color'); ?></dt>
		<dd>
			<?php echo h($classification['Classification']['bg_color']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Classification'), array('action' => 'edit', $classification['Classification']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Classification'), array('action' => 'delete', $classification['Classification']['id']), null, __('Are you sure you want to delete # %s?', $classification['Classification']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Classifications'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Classification'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Items'), array('controller' => 'items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Item'), array('controller' => 'items', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Items'); ?></h3>
	<?php if (!empty($classification['Item'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Dataset Id'); ?></th>
		<th><?php echo __('Capfile Id'); ?></th>
		<th><?php echo __('Capture Date'); ?></th>
		<th><?php echo __('Flow Info'); ?></th>
		<th><?php echo __('Classification Id'); ?></th>
		<th><?php echo __('Ip Src'); ?></th>
		<th><?php echo __('Ip Dst'); ?></th>
		<th><?php echo __('Dns'); ?></th>
		<th><?php echo __('Port Src'); ?></th>
		<th><?php echo __('Port Dst'); ?></th>
		<th><?php echo __('L7prot'); ?></th>
		<th><?php echo __('Lat'); ?></th>
		<th><?php echo __('Long'); ?></th>
		<th><?php echo __('Country'); ?></th>
		<th><?php echo __('Bsent'); ?></th>
		<th><?php echo __('Brecv'); ?></th>
		<th><?php echo __('Blsent'); ?></th>
		<th><?php echo __('Blrecv'); ?></th>
		<th><?php echo __('Pktsent'); ?></th>
		<th><?php echo __('Pktrecv'); ?></th>
		<th><?php echo __('Tracesent'); ?></th>
		<th><?php echo __('Tracerecv'); ?></th>
		<th><?php echo __('Duration'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($classification['Item'] as $item): ?>
		<tr>
			<td><?php echo $item['id']; ?></td>
			<td><?php echo $item['dataset_id']; ?></td>
			<td><?php echo $item['capfile_id']; ?></td>
			<td><?php echo $item['capture_date']; ?></td>
			<td><?php echo $item['flow_info']; ?></td>
			<td><?php echo $item['classification_id']; ?></td>
			<td><?php echo $item['ip_src']; ?></td>
			<td><?php echo $item['ip_dst']; ?></td>
			<td><?php echo $item['dns']; ?></td>
			<td><?php echo $item['port_src']; ?></td>
			<td><?php echo $item['port_dst']; ?></td>
			<td><?php echo $item['l7prot']; ?></td>
			<td><?php echo $item['lat']; ?></td>
			<td><?php echo $item['long']; ?></td>
			<td><?php echo $item['country']; ?></td>
			<td><?php echo $item['bsent']; ?></td>
			<td><?php echo $item['brecv']; ?></td>
			<td><?php echo $item['blsent']; ?></td>
			<td><?php echo $item['blrecv']; ?></td>
			<td><?php echo $item['pktsent']; ?></td>
			<td><?php echo $item['pktrecv']; ?></td>
			<td><?php echo $item['tracesent']; ?></td>
			<td><?php echo $item['tracerecv']; ?></td>
			<td><?php echo $item['duration']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'items', 'action' => 'view', $item['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'items', 'action' => 'edit', $item['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'items', 'action' => 'delete', $item['id']), null, __('Are you sure you want to delete # %s?', $item['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Item'), array('controller' => 'items', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
