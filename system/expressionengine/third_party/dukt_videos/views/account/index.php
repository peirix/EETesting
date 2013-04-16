<div class="dv-mcp-accounts">
	<table class="">
		<tbody>
			<?php
			foreach($services as $service)
			{
				$is_authenticated = $service->is_authenticated();

				?>

				<tr>
					<td class="dv-mcp-col-status">
						<a href="<?php
						if(!$service->enabled && $is_authenticated)
						{
							echo $links[$service->service_key]['enable'];
						}
						else
						{
							echo $links[$service->service_key]['disable'];
						}
						?>" class="dv-mcp-<?php
						if($service->enabled && $is_authenticated)
						{
							echo 'enabled';
						}
						else
						{
							echo 'disabled';
						}
						?>"><?php
						if($service->enabled && $is_authenticated)
						{
							echo $app->lang_line('disable');
						}
						else
						{
							echo $app->lang_line('enable');
						}
						?></a>
					</td>
					
					<td>
						<span class="dv-mcp-service-<?php echo $service->service_key?>">
							<?php echo $service->service_name?>
						</span>
					</td>
	
					<td class="dv-mcp-col-configure">
						<a href="<?php
						
						echo $links[$service->service_key]['configure'];
						
						?>" class="dv-btn"><?php echo $app->lang_line('configure');?></a>
					</td>
	
					<td class="dv-mcp-col-enable">
						<a href="<?php
						if(!$service->enabled && $is_authenticated)
						{
							echo $links[$service->service_key]['enable'];
						}
						else
						{
							echo $links[$service->service_key]['disable'];
						}
						?>" class="dv-btn<?php
						if(!$is_authenticated)
						{
							echo ' dukt-videos-btn-disabled';
						}
						?>">
						<?php
						if($service->enabled && $is_authenticated)
						{
							echo $app->lang_line('disable');
						}
						else
						{
							echo $app->lang_line('enable');
						}
						?>
						</a>
					</td>
				</tr>
				
				<?php
			}
			?>
		</tbody>
	</table>
</div>