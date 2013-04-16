<?php

/**
 * Dukt Videos
 *
 * @package		Dukt Videos
 * @version		Version 1.0
 * @author		Benjamin David
 * @copyright	Copyright (c) 2013 - DUKT
 * @link		http://dukt.net/videos/
 *
 */

namespace DuktVideos;

// ------------------------------------------------------------------------

/**
 * App Interface
 *
 */
interface iService
{
	public function get_video_id($url);
	public function metadata($video);
	public function connect($lib, $app);
	public function connect_callback($lib, $app);	
}

/* END App Interface */

/* End of file app.php */
/* Location: ./dukt-videos-universal/interfaces/app.php */