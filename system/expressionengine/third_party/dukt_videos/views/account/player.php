<h2>Player Settings</h2>

<form method="post" action="<?php echo admin_url('admin.php?page=dukt-videos-player-settings&method=player_settings_save')?>">
	<?php
	foreach($services as $service)
	{
		?>
		<h3><?php echo $service->service_name?></h3>	
	
		<?php
		foreach($service->embed_options as $k => $v)
		{
			?>
			<p>
				<label><?php echo $k?></label><br />
				<input type="text" name="<?php echo $service->service_key.'_player_'.$k?>" value="<?php echo $app->get_option($service->service_key, 'player_'.$k, $v)?>" />
			</p>
			<?php
		}
	}
	?>

	<input type="submit" class="button-primary" value="Save Player Options" />
</form>