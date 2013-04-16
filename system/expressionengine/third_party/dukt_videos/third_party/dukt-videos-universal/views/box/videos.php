<!-- videos -->

<?php

if(count($videos) > 0)
{
	if(!isset($pagination['next_page']))
	{
		$pagination['next_page'] = false;
	}

	if($pagination['next_page'] === 2)
	{
		echo '<ul>';
	}

	foreach($videos as $video)
	{
		?>
		<li onclick="" data-video-page="<?php echo $video['url']?>">
			<div class="dv-videos-thumb">
				<span class="dv-clip-outer">
					<span class="dv-clip">
						<span class="dv-clip-inner">
							<img src="<?php echo $video['thumbnail']?>" />
							<span class="dv-vertical-align"></span>
						</span>
					</span>
				</span>
			</div>
			<div class="dv-videos-text">
				<strong><?php 
				$max = 45;
				if(strlen($video['title']) > $max)
				{
					echo trim(substr($video['title'], 0, $max))."&hellip;";
				}
				else
				{
					echo $video['title'];
				}
				?></strong>
				<small>
					<?php echo $video['plays']?> <?php echo $app->lang_line('plays'); ?> - <?php
					
					$date_fmt = $app->userdata('time_format');
					
					if ($date_fmt == 'us')
					{
						$datestr = '%m/%d/%Y';
					}
					else
					{
						$datestr = '%d/%m/%Y';
					}
					
					echo strftime($datestr, $video['date']);

					?><br />
					<?php echo $app->lang_line('from'); ?> <?php echo $video['author_username']?>
				</small>
			</div>
			<div class="dv-clear"></div>
		</li>
		<?php
	}

	if($pagination['next_page'] && count($videos) == $pagination['per_page'])
	{
		?>
		<li class="dv-videos-more" data-next-page="<?php echo $pagination['next_page']?>">
			<span class="dv-videos-more-btn"><?php echo $app->lang_line('load_more_videos'); ?></span>
			<span class="dv-videos-more-loading"><?php echo $app->lang_line('loading_videos'); ?>...</span>
		</li>
		<?php
	}

	if($pagination['next_page'] === 2)
	{
		echo '</ul>';
	}
}
else
{
	if($pagination['page'] == 1)
	{
		if(empty($q) && $this->input_post('method') == "service_search")
		{
			?>
			<p class="dv-videos-empty"><?php echo $app->lang_line('search_'.$service->service_key.'_videos'); ?></p>
			<?php
		}
		else
		{
			?>
			<p class="dv-videos-empty"><?php echo $app->lang_line('no_videos'); ?></p>
			<?php
		}
	}
}
?>