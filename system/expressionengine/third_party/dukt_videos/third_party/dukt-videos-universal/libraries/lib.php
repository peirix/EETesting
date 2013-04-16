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

class Lib {
	
	var $language = array();
	var $load_view_current_vars = false;
	
	function __construct($options)
	{
		$this->base_path = $options['basepath'];
		
		$this->lang_load('dukt_video');
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Load View
	 *
	 * @access	public
	 * @return	string
	 */
	function load_view($view, $vars=false, $return = false, $cms = false)
	{
		if($vars)
		{
			foreach($vars as $k => $v)
			{
				${$k} = $v;
			}
		}
		
		ob_start();
		
		if(!$cms)
		{
			include(DUKT_VIDEOS_UNIVERSAL_PATH.'views/'.$view.'.php');
		}
		else
		{
			include(DUKT_VIDEOS_PATH.'views/'.$view.'.php');
		}
		
		$buffer = ob_get_contents();
		
		@ob_end_clean();

		if($return)
		{
			return $buffer;	
		}
		else
		{
			echo $buffer;
		}
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Load Library
	 *
	 * @access	public
	 * @return	string
	 */
	function load_library($library)
	{	
		include_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/'.$library.'.php');
		
		$library_object = new $library;
		
		return $library_object;
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Load Helper
	 *
	 * @access	public
	 * @return	string
	 */
	function load_helper($helper)
	{
		include_once(DUKT_VIDEOS_UNIVERSAL_PATH.'helpers/'.$helper.'_helper.php');
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Load Lang
	 *
	 * @access	public
	 * @return	string
	 */
	function lang_load($lang_file, $language = 'english')
	{
		$file = DUKT_VIDEOS_UNIVERSAL_PATH.'language/'.$language.'/'.$lang_file.'_lang.php';

		if(file_exists($file))
		{
			include(DUKT_VIDEOS_UNIVERSAL_PATH.'language/'.$language.'/'.$lang_file.'_lang.php');
			
			$merged_array = array_merge($this->language, $lang);
			
			$this->language = $merged_array;
		}
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Load Config
	 *
	 * @access	public
	 * @return	string
	 */
	function config_load($config)
	{
		include(DUKT_VIDEOS_UNIVERSAL_PATH.'config/'.$config.'.php');
	}
	
	// ------------------------------------------------------------------------------

	/**
	 * Get config item
	 *
	 * @access	public
	 * @return	string
	 */
	public function config_item($item)
	{
		include(DUKT_VIDEOS_UNIVERSAL_PATH.'config/dukt_video.php');
		return $config[$item];
	}
	
	// ------------------------------------------------------------------------------

	/**
	 * Input : Get
	 *
	 * @access	public
	 * @return	string
	 */
	function input_get($key)
	{
		if(isset($_GET[$key]))
		{
			return $_GET[$key];
		}
		
		return false;
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Input : Post
	 *
	 * @access	public
	 * @return	string
	 */
	function input_post($key)
	{
		if(isset($_POST[$key]))
		{
			return $_POST[$key];
		}
		
		return false;
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Session : Set userdata
	 *
	 * @access	public
	 * @return	string
	 */
	function session_set_userdata($k, $v)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		$_SESSION[$k] = $v;
		
		return $v;
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Session : Get userdata
	 *
	 * @access	public
	 * @return	string
	 */
	function session_userdata($k)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		if(isset($_SESSION[$k]))
		{
			return $_SESSION[$k];
		}
		
		return NULL;
	}

	// ------------------------------------------------------------------------------

	/**
	 * Session : Unset userdata
	 *
	 * @access	public
	 * @return	void
	 */
	function session_unset_userdata($k)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		if(isset($_SESSION[$k]))
		{
			unset($_SESSION[$k]);
		}
	}
}

/* END Lib Class */

/* End of file lib.php */
/* Location: ./dukt-videos-universal/libraries/lib.php */