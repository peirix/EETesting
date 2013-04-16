<!-- listing my videos -->
<div class="dv-listing-view" data-service="<?php echo $service->service_key?>" data-listing="my-videos">

	<div class="dv-top">
		<div class="dv-splitter-white-left">
			<div class="dv-splitter-black-right">
				<div class="dv-splitter-black-top">
					<div class="dv-splitter-black-bottom">
						<h2 class="dv-title"><?php echo $app->lang_line('my_videos'); ?></h2>
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