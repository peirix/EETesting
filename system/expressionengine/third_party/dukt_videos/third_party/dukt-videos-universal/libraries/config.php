<?php
namespace DuktVideos;

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
require_once(DUKT_VIDEOS_PATH.'libraries/app.php');

class Config {

	/**
	 * Get config item
	 *
	 * @access	public
	 * @return	string
	 */
	public static function item($k)
	{
		if(file_exists(DUKT_VIDEOS_PATH.'config.php'))
		{		
			require(DUKT_VIDEOS_PATH.'config.php');	
		}
		else
		{
			require(DUKT_VIDEOS_PATH.'config/dukt_video.php');	
		}
		
		if(isset($config[$k]))
		{
			return $config[$k];
		}
		
		return false;
	}
}


/* END Config Class */

/* End of file config.php */
/* Location: ./dukt-videos-universal/libraries/config.php */