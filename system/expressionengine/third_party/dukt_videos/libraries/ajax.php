<?php

/**
 * Dukt Videos
 *
 * @package		Dukt Videos
 * @version		Version 1.0.2
 * @author		Benjamin David
 * @copyright	Copyright (c) 2013 - DUKT
 * @link		http://dukt.net/add-ons/expressionengine/dukt-videos/
 *
 */
 
namespace DuktVideos;

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/ajax.php');
require_once(DUKT_VIDEOS_PATH.'libraries/app.php');
 
class Ajax_expressionengine extends Ajax {

	public function __construct()
	{
		parent::__construct();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Field Preview
	 *
	 * @access	public
	 * @return	array
	 */
	public function field_preview()
	{
		$services = \DuktVideos\App::get_services();
		
		$video_url = $this->lib->input_post('video_page');
		
		
		// get video
		
		$vars['video'] = \DuktVideos\App::get_video($video_url);
		
		
		// get embed
				
		$embed_options = array(
			'disable_size' => true,
			'autohide' => true
		);
		
		if(!isset($services[$vars['video']['service_key']]))
		{
			return "Couldn't connect to video service";	
		}
		
		$service = $services[$vars['video']['service_key']];
		
		$vars['embed'] = $service->get_embed($vars['video']['id'], $embed_options);
    	
		echo $this->lib->load_view('field/preview', $vars, true, 'expressionengine');
		
		exit;
	}
	
	// --------------------------------------------------------------------
}

// END Ajax_expressionengine class

/* End of file ajax.php */
/* Location: ./system/expressionengine/libraries/ajax.php */