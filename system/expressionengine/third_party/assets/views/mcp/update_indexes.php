<?php
	foreach ($source_list as $key => $name)
	{
		echo '<div class="assets-sync-item sync_' . $key . '" ><label for="' . $key . '">' . $name . '</label> <input type="checkbox" class="indexing" id="' . $key .'" /></div>';
	}
?>
<br />
<input type="submit" class="submit assets-index" value="<?php echo lang('update_indexes') ?>" />
<div id="assets-dialog">
	<div id="index-message"></div>
	<div id="index-status-report"></div>
</div>
