<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
 
require_once PATH_THIRD.'dukt_videos/config.php';

class Dukt_videos {

	var $return_data = '';

	/**
	 * Constructor
	 *
	 */
	function Dukt_videos()
	{
		$this->EE =& get_instance();
		
		
		// load dukt videos
		
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
		
		require_once(DUKT_VIDEOS_PATH.'libraries/app.php');
		
		$this->lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		
		$this->EE->load->helper('url');
		
		$this->app = new \DuktVideos\App;
		
		$this->services = $this->app->get_services();
		
		
		// default method
		
		$this->details();
	}
	
	// --------------------------------------------------------------------	

	/**
	 * Details
	 *
	 * @access	public
	 * @return	string
	 */
	public function details()
	{
		$out = "";

		if(!isset($this->EE->TMPL->tagdata))
		{			
			return false;
		}

		$tagdata = $this->EE->TMPL->tagdata;


		// get video from url
		
		$video_url = $this->EE->TMPL->fetch_param('url');
		
		$video = $this->app->get_video($video_url, true);
		
		if(!$video)
		{
			$this->EE->TMPL->log_item("Dukt Videos : Couldn't get video");
			
			return false;
		}
		
		
		// get service
		
		$service = $this->services[$video['service_key']];
		
		if(!$service)
		{
			$this->EE->TMPL->log_item("Dukt Videos : Couln't get service");
			
			return false;
		}
		
		
		// get embed

		$embed_opts = $this->EE->TMPL->tagparams;

		$embed = $service->get_embed($video['id'], $embed_opts);
		
		$video['embed'] = $embed;
		

		// no tagdata ? return the embed

		if(!$tagdata)
		{
			return $this->return_data = $video['embed'];
		}


		// parse tagdata

		$local_date = $video['date'];

		if (preg_match_all("#".LD."date format=[\"|'](.+?)[\"|']".RD."#", $tagdata, $matches))
		{
			foreach ($matches['1'] as $match)
			{
				$tagdata = preg_replace("#".LD."date format=.+?".RD."#", $this->EE->localize->decode_date($match, $local_date), $tagdata, 1);
			}
		}

		$conditionals = $this->EE->functions->prep_conditionals($tagdata, $video);

		$out = $this->EE->functions->var_swap($conditionals, $video);

		return $this->return_data = $out;
	}
	
	// --------------------------------------------------------------------	

	/**
	 * Single : alias for details
	 *
	 * @access	public
	 * @return	string
	 */	
	
	public function single()
	{
		return $this->details();
	}
	
	// --------------------------------------------------------------------	

	/**
	 * Pair : alias for details
	 *
	 * @access	public
	 * @return	string
	 */
	
	public function pair()
	{
		return $this->details();
	}

	// --------------------------------------------------------------------

	/**
	 * Front Endpoint for Ajax Calls
	 *
	 * @access	public
	 * @return	tagdata
	 */
	public function ajax()
	{
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/ajax.php');
		require_once(DUKT_VIDEOS_PATH.'libraries/ajax.php');
		
		$ajax = new \DuktVideos\Ajax_expressionengine();
		
		$method = $this->EE->input->post('method');
		
		if($method)
		{
			$ajax->{$method}();
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Callback
	 *
	 * @access	public
	 * @return	void
	 */
	public function callback()
	{
		$service_key =  $this->lib->input_get('service');
		
		if($service_key)
		{
			$lib = $this->lib;
			$app = $this->app;
			
			$services = $app->get_services();
			
			$services[$service_key]->connect_callback($lib, $app);
		}
	}	
}

/* END Dukt_videos Class */

/* End of file mod.dukt_videos.php */
/* Location: ./system/expressionengine/third_party/dukt_videos/mod.dukt_videos.php */