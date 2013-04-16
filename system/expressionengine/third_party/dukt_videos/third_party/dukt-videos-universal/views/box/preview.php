<!-- preview -->

<?php
if($video)
{
	?>
	<div class="dv-current" data-video-url="<?php echo $video['url']?>">
		<div class="dv-controls">	
			<a onclick="" class="dv-preview-fullscreen">
				<?php echo $app->lang_line('fullscreen'); ?>
			</a>
	
			<div class="dv-splitter-light-top"></div>
	
			<a onclick="" class="dv-preview-favorite<?php
			if($video['is_favorite'])
			{
				echo " dv-preview-favorite-selected";
			}
			?>" data-service="<?php echo $service?>"><?php echo $app->lang_line('add_favorite'); ?></a>
	
		</div>
	
		<div class="dv-preview-video">
			<?php
			echo $embed;
			?>
		</div>
	
		<div class="dv-preview-description">
			<div class="dv-preview-description-in">
				<?php
		
				function makeClickableLinks($text) {
				   $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
				   $callback = create_function('$matches', '
				       $url       = array_shift($matches);
				       $url_parts = parse_url($url);
				
				       $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
				       $text = preg_replace("/^www./", "", $text);
				
				       $last = -(strlen(strrchr($text, "/"))) + 1;
				       if ($last < 0) {
				           $text = substr($text, 0, $last) . "&hellip;";
				       }
				
				       return sprintf(\'<a rel="nofollow" href="%s">%s</a>\', $url, $text);
				   ');
				
				   return preg_replace_callback($pattern, $callback, $text);
				}
		
				$description = $video['description'];
		
				if(strlen($description) > 0)
				{
					echo (makeClickableLinks($description));
				} else {
					?>
					<p class="dv-preview-description-empty"><?php echo $app->lang_line('no_description'); ?></p>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
}
