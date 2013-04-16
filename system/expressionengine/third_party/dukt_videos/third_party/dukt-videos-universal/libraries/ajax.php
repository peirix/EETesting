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

class Ajax {

	public function __construct()
	{
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
		require_once(DUKT_VIDEOS_PATH.'libraries/app.php');
					
		$this->lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		$this->app = new \DuktVideos\App;

		$language = \DuktVideos\App::current_language();
		
		$this->lib->load_helper('url');
		$this->lib->lang_load('dukt_videos', $language);
		
		$this->services = \DuktVideos\App::get_services();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * @access	public
	 * @return	string
	 */
	public function index()
	{	
		$service = $this->lib->input_post('service');
		$method = $this->lib->input_post('method');

		if($method && $service)
		{
			$this->{$method}();	
		}
	}
		
	// --------------------------------------------------------------------

	/**
	 * Box
	 *
	 * @access	public
	 * @return	string
	 */
	public function box()
	{		
		$vars = array();
		$vars['services'] = $this->services;
		$vars['app'] = $this->app;
		
		$this->lib->load_view('box/box', $vars);
		
		exit;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Service Search
	 *
	 * @access	public
	 * @return	string
	 */
	public function service_search()
	{	
		$service = $this->lib->input_post('service');
		$service = $this->services[$service];
		
		$q = $this->lib->input_post('q');
	
		try
		{
			$videos = array();
			
			$pagination = $this->pagination();
				
			$vars['videos'] = $videos;
			$vars['pagination'] = $pagination;
			$vars['service'] = $service;
			$vars['app'] = $this->app;
			
			if(!empty($q))
			{
				$videos = $service->search($q, $pagination['page'], $pagination['per_page']);
			}
			
			$vars['videos'] = $videos;
			
			echo $this->lib->load_view('box/videos', $vars, true);
			
			exit;
		}
		catch(Exception $e)
		{
			\DuktVideos\App::developer_log($e->getMessage());
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Service favorites
	 *
	 * @access	public
	 * @return	string
	 */
	public function service_favorites()
	{
		$service = $this->lib->input_post('service');

		$service = $this->services[$service];
			
		try
		{
			$videos = array();

			$pagination = $this->pagination();
			
			$videos = $service->get_favorites($pagination['page'], $pagination['per_page']);

			$vars['videos'] = $videos;
			$vars['pagination'] = $pagination;
			$vars['app'] = $this->app;
			
			echo $this->lib->load_view('box/videos', $vars, true);
			
			exit;
		}
		catch(Exception $e)
		{
			\DuktVideos\App::developer_log($e->getMessage());
		}	
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set/Unset favorite for a given video
	 *
	 * @access	public
	 * @return	string
	 */
	public function favorite()
	{
		$service = $this->lib->input_post('service');
		$service = $this->services[$service];


		$video_page = $this->lib->input_post('video_page');

		$video_id = $service->get_video_id($video_page);


		// check if already a fav

		$already_fav = $service->is_favorite($video_id);


		if($already_fav)
		{
			// remove favorite if it is
			
			$service->remove_favorite($video_id);
		}
		else
		{
			// add favorite if it's not

			$service->add_favorite($video_id);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Service Videos
	 *
	 * @access	public
	 * @return	string
	 */
	public function service_videos()
	{
		$service = $this->lib->input_post('service');
		$service = $this->services[$service];
	
		try {
			$videos = array();

			$pagination = $this->pagination();
			
			$videos = $service->get_videos($pagination['page'], $pagination['per_page']);

			$vars['videos'] = $videos;
			$vars['pagination'] = $pagination;
			$vars['app'] = $this->app;
			
			echo $this->lib->load_view('box/videos', $vars, true);
			
			exit;
		}
		catch(Exception $e)
		{
			\DuktVideos\App::developer_log($e->getMessage());
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Box Preview
	 *
	 * @access	public
	 * @return	string
	 */
	public function box_preview()
	{
		// get post data
		
		$video_url = $this->lib->input_post('video_page');

		
		// get video
		
		$video = \DuktVideos\App::get_video($video_url);
		
		
		// get service
		
		$service = $video['service_key'];
		
		if(!isset($this->services[$service]))
		{
			return false;
		}
		
		$service = $this->services[$service];
		
		
		// get embed
		
		$embed_options = array(
			'width' => false,
			'height' => false,
			'youtube_autohide' => 1,
			'youtube_controls' => 0
		);
		
		foreach($service->embed_options as $k => $v)
		{
			if($this->lib->input_post($k))
			{
				$embed_options[$k] = $this->lib->input_post($k);
			}
		}
		
		$embed = $service->get_embed($video['id'], $embed_options);


		// is_favorite ?
		
		$video['is_favorite'] = $service->is_favorite($video['id']);
		
		
		// load view

		$vars['video'] = $video;
		$vars['service'] = $service->service_key;
		$vars['embed'] = $embed;
		$vars['app'] = $this->app;
		
		echo $this->lib->load_view('box/preview', $vars, true);	
		
		exit;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Pagination
	 *
	 * @access	public
	 * @return	string
	 */
	private function pagination()
	{
		$pagination['per_page'] = 25;

		$pagination['page'] = $this->lib->input_post('page');

		if(!$pagination['page'])
		{
			$pagination['page'] = 1;
		}

		$pagination['next_page'] = $pagination['page'] + 1;

		return $pagination;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Error
	 *
	 * @access	public
	 * @return	string
	 */
	public function error($msg)
	{
		echo $msg;
	}
	
	// --------------------------------------------------------------------
}


/* END Ajax Class */

/* End of file ajax.php */
/* Location: ./dukt-videos-universal/libraries/ajax.php */