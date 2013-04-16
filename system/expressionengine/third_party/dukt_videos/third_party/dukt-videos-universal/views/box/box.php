<!-- .dukt-videos-box -->

<div class="dv-box">

	<div class="dv-box-in<?php
	
	if($this->input_post('method'))
	{
		// lightbox mode
	
		echo ' lightbox';
	}
	
	?>">
		<!-- .dukt-videos-accounts -->
	
		<div class="dv-col dv-accounts">
	
			<div class="dv-top">
				<span class="dv-splitter-black-right">
					<span class="dv-splitter-black-bottom">
						<div class="dv-splitter-black-top">
							<div class="dv-splitter-black-left">
								<a class="dv-close">
									<?php echo $app->lang_line('close'); ?>
								</a>
							</div>
						</div>
					</span>
				</span>
			</div>
	
			<!-- .dukt-videos-services -->
	
			<ul class="dv-services">
				<?php
	
				foreach($services as $service)
				{
					if($service->enabled && $service->is_authenticated())
					{
						?>
						<li class="dv-service <?php echo $service->service_key?>" data-service="<?php echo $service->service_key?>">
							<a class="dv-service" onclick="" data-method="videos" data-service="<?php echo $service->service_key?>"><?php echo $service->service_key?></a>
							<ul class="dv-actions">
								<li><a onclick="" data-service="<?php echo $service->service_key?>" data-listing="search" data-method="service_search" class="dv-search"><?php echo $app->lang_line('search'); ?></a></li>
								<li><a onclick="" data-service="<?php echo $service->service_key?>" data-listing="my-videos" data-method="service_videos" class="dv-my-videos"><?php echo $app->lang_line('my_videos'); ?></a></li>
								<li><a onclick="" data-service="<?php echo $service->service_key?>" data-listing="favorites" data-method="service_favorites" class="dv-favorites"><?php echo $app->lang_line('favorites'); ?></a></li>
	<!-- 							<li><a data-service="<?php echo $service->service_key?>" data-listing="playlists" data-method="service_playlists" class="dv-dukt-videos-service-playlists"><?php echo $app->lang_line('Playlists'); ?></a></li> -->
							</ul>
						</li>
						<?php
					}
				}
				?>
	
			</ul>
	
			<!-- /.dukt-videos-services -->
	
			<div class="dv-bottom">
				<span class="dv-splitter-black-top">
					<span class="dv-splitter-black-right">
						<span class="dv-splitter-black-bottom">
							<span class="dv-splitter-black-left">
							
								<?php
								if(isset($manage_link))
								{
									?>
									<a class="dv-btn-manage" href="<?php echo $manage_link?>"><?php echo $app->lang_line('configure'); ?></a>
									<?php
								}
								?>
								
								<div class="dv-status">
								
									<div class="dv-spin"></div>
									
									<div class="dv-reload" onclick=""></div>
									
								</div>
			
							</span>
						</span>
					</span>
				</span>
			</div>
			
			<!-- .bottom -->
			
		</div>
	
		<!-- /.dukt-videos-accounts -->
	
	
		<!-- .dukt-videos-listings -->
	
		<div class="dv-col dv-listings">
			<?php
			
				foreach($services as $service)
				{
					if($service->enabled && $service->is_authenticated())
					{
						$vars = array('service' => $service, 'app' => $app);
						
						$this->load_view('box/listing-search', $vars);
						$this->load_view('box/listing-my-videos', $vars);
						$this->load_view('box/listing-favorites', $vars);
						$this->load_view('box/listing-playlists', $vars);			
					}
				}
			?>
		</div>
	
		<!-- /.dukt-videos-listings -->
	
	
	
		<!-- .dukt-videos-preview -->
	
		<div class="dv-col dv-preview">
			<div class="dv-spin"></div>
			<div class="dv-top">
				<div class="dv-splitter-white-left">
					<div class="dv-splitter-black-bottom">
						<div class="dv-splitter-black-top">
							<div class="dv-splitter-black-right">
								
							</div>
						</div>
					</div>
				</div>
			</div>
	
			<div class="dv-preview-inject"></div>
	
	
			<div class="dv-bottom">
				<div class="dv-splitter-white-left">
					<div class="dv-splitter-black-top">
						<div class="dv-splitter-black-right">
							<div class="dv-splitter-black-bottom">
			
								<div class="dv-controls">
									<div class="dv-controls-in">
										<a class="dv-submit dv-btn"><?php echo $app->lang_line('select_video'); ?></a>
										<a class="dv-cancel dv-btn"><?php echo $app->lang_line('cancel'); ?></a>
										<div class="dv-clear"></div>
									</div>
								</div>
			
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	
		<!-- /.dukt-videos-preview -->
	
	</div>

<!-- /.dukt-videos-box -->

</div>