<?php
namespace DuktVideos;

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
require_once(DUKT_VIDEOS_PATH.'libraries/app.php');

class Cache {

	public function __construct()
	{					
		$this->lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		
		$this->app = new \DuktVideos\App;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Create cache folder if it doesn't exist yet
	 *
	 * @access	public static
	 * @return	void
	 */
	public static function create_cache_folder()
	{
		$cache_path = \DuktVideos\App::cache_path();
		
		// create cache folder if it doesn't exists
		
		if(!file_exists($cache_path))
		{
			if(!@mkdir($cache_path, 0777))
			{
				\DuktVideos\App::developer_log("Couldn't create cache folder");
			}
			
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get cache file
	 *
	 * @access	public static
	 * @param	string
	 * @return	string
	 */
	public static function get($id)
	{
		self::create_cache_folder();
	
		$cache_path = \DuktVideos\App::cache_path();
		
		$id = md5($id);
			
		if (!file_exists($cache_path.$id))
		{
			return FALSE;
		}
		
		$lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));

		$lib->load_helper('file');

		$data = read_file($cache_path.$id);

		$data = unserialize($data);

		if (time() >  $data['time'] + $data['ttl'])
		{
			unlink($cache_path.$id);
			
			return FALSE;
		}

		return $data['data'];
	}

	// --------------------------------------------------------------------

	/**
	 * Save cache file with time to live
	 *
	 * @access	public static
	 * @param	string
	 * @param	string
	 * @param	integer
	 * @return	bool
	 */
	public static function save($id, $data, $ttl = 60)
	{
		self::create_cache_folder();
		
		$lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		$lib->load_helper('file');
		
		
		// get cache path
		
		$cache_path = \DuktVideos\App::cache_path();
		
		if(!$cache_path)
		{
			return false;
		}

		$id = md5($id);
	
		$contents = array(
			'time'		=> time(),
			'ttl'		=> $ttl,
			'data'		=> $data
		);
		
		if(write_file($cache_path.$id, serialize($contents)))
		{	
			@chmod($cache_path.$id, 0777);
			
			return TRUE;
		}

		return FALSE;
	}
}


/* END Cache Class */

/* End of file cache.php */
/* Location: ./dukt-videos-universal/libraries/cache.php */