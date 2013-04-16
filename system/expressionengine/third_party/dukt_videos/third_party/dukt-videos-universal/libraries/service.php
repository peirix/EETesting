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

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
require_once(DUKT_VIDEOS_PATH.'libraries/app.php');
require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/cache.php');
require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/config.php');

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'interfaces/service.php');

class Service {

	var $options = false;

	var $embed_options = array(
		'autoplay'			=> false,
		'disable_size'		=> false,
		'height'			=> "282", 
		'loop'				=> false,
		'width'				=> "500",		
	);

	var $model_options = array(
		'service_key' => false,
		'service_name' => false,
		'id' => false,
		'url' => false,
		'date' => false,
		'plays' => false,
		'duration' => false,
		'duration_seconds' => false,
		'author_name' => false,
		'author_url' => false,
		'author_username' => false,
		'thumbnail' => false,
		'thumbnail_large' => false,
		'embed' => false,
		'title' => false,
		'description' => false,
	);
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Duration from seconds to h:m:s
	 *
	 * @access	public
	 * @return	array
	 */
	public function duration($sec, $padHours = false)
	{
		$hms = "";
		$hours = intval(intval($sec) / 3600); 
		
		$hms .= ($padHours) 
		? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
		: $hours. ":";
		
		$minutes = intval(($sec / 60) % 60); 
		
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
		
		$seconds = intval($sec % 60); 
		
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
		
		return $hms;
	}
	
	// ------------------------------------------------------------------------------
	
	/**
	 * Get video
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_video($video_url, $cache = false)
	{
		// get video id
		
		$video_id = $this->get_video_id($video_url);
		
		if(!$video_id)
		{
			return false;
		}
		
		$video = false;
		$cache_id = $this->service_key.$video_id;

		if($cache)
		{
			// load video metadata with cache layer
			
			$video = \DuktVideos\Cache::get($cache_id);
		}
		
		if(!$video)
		{
			$ttl = \DuktVideos\Config::item('cache_ttl');
			
			$video = $this->metadata($video_id);

			\DuktVideos\Cache::save($cache_id, $video, $ttl);
		}
		

		return $video;
	}
	
	// --------------------------------------------------------------------	
	
	/**
	 * Get embed
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_embed($video_id, $user_embed_opts)
	{		
		// embed opts :  use default or user defined embed options
		

		$common_opts = new \DuktVideos\Service;
		$common_opts = $common_opts->embed_options;
		
		$embed_opts = $this->embed_options;
		
		
		// user defined common opts
		
		foreach($common_opts as $k => $v)
		{
			if(isset($user_embed_opts[$k]))
			{
				$embed_opts[$k] = $user_embed_opts[$k];
			}
		}
		
		
		// user defined service specific opts
		
		foreach($this->embed_options as $k => $v)
		{
			if(isset($user_embed_opts[$this->service_key."_".$k]))
			{
				$embed_opts[$k] = $user_embed_opts[$this->service_key."_".$k];
			}
		}
		
		
		// let boolean parameters accept yes/no and 1/0 values
		
		foreach($embed_opts as $k => $v)
		{
			if($v == "yes" || $v === "1" || $v === 1 || $v === true)
			{
				$embed_opts[$k] = 1;
			}
			elseif($v == "no" || $v === "0" || $v === 0 || $v === false)
			{
				$embed_opts[$k] = 0;
			}
		}
		
		
		// build embed
		
		$query_embed_opts = $embed_opts;
		
		if(isset($query_embed_opts['width']))
		{
			$query_embed_opts['width'] = NULL;
		}
		
		if(isset($query_embed_opts['height']))
		{
			$query_embed_opts['height'] = NULL;
		}

		$opts_query = http_build_query($query_embed_opts);		

		if(!preg_match('/\?/', $this->universal_url, $matches, PREG_OFFSET_CAPTURE))
		{
			$opts_query = '?'.$opts_query;
		}
		else
		{
			$opts_query = '&'.$opts_query;
		}
		
		if(isset($embed_opts['disable_size']) && $embed_opts['disable_size'] === "yes" || $embed_opts['disable_size'] === true)
		{
			$format = '<iframe src="'.$this->universal_url.$opts_query.'" frameborder="0" allowfullscreen="true" allowscriptaccess="true"></iframe>';
			
			$embed = sprintf($format, $video_id);
		}
		else
		{
			$format = '<iframe src="'.$this->universal_url.$opts_query.'" width="%s" height="%s" frameborder="0" allowfullscreen="true" allowscriptaccess="true"></iframe>';
			
			$embed = sprintf($format, $video_id, $embed_opts['width'], $embed_opts['height']);
		}

		return $embed;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Throw Exception
	 *
	 * @access	public
	 * @return	void
	 */
	public function handle_exception($e, $class, $function)
	{
		$msg = '';
		
		$msg .= 'file : '.$e->getFile().'<br />';
		$msg .= 'line number : '.$e->getLine().'<br />';
		$msg .= 'class : '.$class.'<br />';
		$msg .= 'method : '.$function.'<br />';
		$msg .= 'error : ';
		$msg .= strip_tags($e->getMessage());
		
		\DuktVideos\App::developer_log($msg);	
	}
}


/* END Service Class */

/* End of file service.php */
/* Location: ./dukt-videos-universal/libraries/service.php */