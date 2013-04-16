<?php
if(!$service->is_authenticated())
{
	?>
	
	<p><?php echo $app->lang_line('connect_website_to');?>  <?php echo $service->service_name?></p>
		
	<?php echo $form_open?>
		
		<fieldset>
			<legend>API Configuration</legend>
			<?php
			echo $app->lang_line($service->service_key.'_api_instructions');
			?>
			<p>
				<strong>Endpoint URL : </strong><?php echo $endpoint_url?>
			</p>
			<?php
			foreach($service->api_options as $k => $v)
			{
				?>
				<p>
					<label><?php echo $app->lang_line(''.$service->service_key."_".$k);?></label><br />
					<input type="text" name="<?php echo $k?>" value="<?php echo $app->get_option($service->service_key, $k)?>" />
				</p>
				<?php
			}
		
			?>
			<div class="margin-form">
				<input type="submit" class="button-primary" name="connect" value="<?php echo $app->lang_line('connect_to');?> <?php echo $service->service_name?>" />
			</div>
			
		</fieldset>
	<?php echo $form_close?>
	
	<?php
}
else
{
	?>
	
		<p><?php echo $app->lang_line('website_connected_to');?> <?php echo $service->service_name?>.</p>
		
		<p><a href="<?php
			echo $links['reset'];
			?>"><?php echo $app->lang_line('reset_connection');?></a></p>
	
	<?php
}
?>