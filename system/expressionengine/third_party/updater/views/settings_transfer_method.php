<div class="utable">
<h2><?=lang($settings['file_transfer_method'])?></h2>
<table class="file_transfer_methods">
	<thead>
		<tr class="heading">
			<th></th>
			<?php foreach($dirs as $dir => $status):?>
			<th><?=lang('dir:'.$dir)?></th>
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody>

		<?php if ($settings['file_transfer_method'] != 'local'): ?>
		<tr>
			<td><?=lang('connect')?></td>
			<?php foreach($dirs as $dir => $status):?>
			<td>
				<?php if ($connect == FALSE):?><span class="label label-important"><?=lang('failed')?></span>
				<?php else:?><span class="label label-success"><?=lang('passed')?></span><?php endif;?>
			</td>
			<?php endforeach;?>
		</tr>
		<?php endif;?>

		<?php foreach($actions as $action):?>
		<tr>
			<td><?=lang($action)?></td>
			<?php foreach($dirs as $dir => $status):?>
			<td>
				<?php if ($status[$action] == FALSE):?><span class="label label-important"><?=lang('failed')?></span>
				<?php else:?><span class="label label-success"><?=lang('passed')?></span><?php endif;?>
			</td>
			<?php endforeach;?>
		</tr>
		<?php endforeach;?>

	</tbody>
</table>
</div>