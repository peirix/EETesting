<?php
if($any_account_exists)
{
	?>
	<div class="dv-field">
		
		<?php
		echo $hidden_input;
		?>
		
		<div class="dv-preview"></div>

		<p class="dv-controls">
			<a class="dv-add"><span></span><?php echo $app->lang_line('add_video'); ?></a>
			<a class="dv-change"><span></span><?php echo $app->lang_line('change_video'); ?></a>
			<a class="dv-remove"><span></span><?php echo $app->lang_line('remove_video'); ?></a>
		</p>
	</div>
	<?php
}
else
{
	echo '<p>'.$app->lang_line('addon_disabled').'</p>';
}
?>
