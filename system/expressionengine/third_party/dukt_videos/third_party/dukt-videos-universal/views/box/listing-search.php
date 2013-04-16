<!-- listing search -->

<div class="dv-listing-view" data-service="<?php echo $service->service_key?>" data-listing="search">

	<div class="dv-top">
		<div class="dv-splitter-black-bottom">
			<div class="dv-splitter-white-left">
				<div class="dv-splitter-black-right">
					<div class="dv-splitter-black-top">
						<!-- .dukt-videos-search -->
						<div class="dv-search">
							<div class="dv-search-bg">
								<div class="dv-search-left">
									<div class="dv-search-right">
										<input type="text" data-service="<?php echo $service->service_key?>" data-listing="search" placeholder="<?php echo $app->lang_line('search_videos'); ?>" />
										<div class="dv-search-reset"></div>
										<div class="dv-spin"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- /.dukt-videos-search -->
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="dv-videos">
		<div class="dv-videos-inject">
			<p class="dv-videos-empty"><?php echo $app->lang_line('no_videos'); ?></p>
		</div>
	</div>

	<div class="dv-bottom">
		<div class="dv-splitter-black-top">
			<div class="dv-splitter-black-right">
				<div class="dv-splitter-white-left">
					<div class="dv-splitter-black-bottom">
		
					</div>
				</div>
			</div>
		</div>
	</div>
</div>