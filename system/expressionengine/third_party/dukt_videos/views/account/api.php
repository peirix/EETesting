<h2>Setup video services</h2>

<?php
foreach($services as $service)
{
	?>
	<h3><?php echo $service->service_name;?></h3>
	
	<form method="post">
		<?php
		foreach($service->api_options as $k => $v)
		{
			?>
			<p>
				<label><?php echo $k?></label><br />
				<input type="text" name="<?php echo $k?>" value="" />
			</p>
			<?php
		}
		?>
		
		<input type="submit" class="button-primary" value="Save Settings" />
	</form>
	<?php
}
?>
